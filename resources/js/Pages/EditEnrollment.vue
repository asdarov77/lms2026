<template>
  <AppLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h5 bg-primary text-white py-3 d-flex">
              <span>Редактирование записи на курс</span>
              <v-spacer></v-spacer>
              <v-btn
                color="white"
                variant="text"
                :to="{ name: 'group-courses' }"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
            </v-card-title>
            
            <div v-if="loading" class="pa-5 text-center">
              <v-progress-circular indeterminate color="primary"></v-progress-circular>
              <p class="mt-2">Загрузка данных...</p>
            </div>
            
            <div v-else-if="error" class="pa-5">
              <v-alert
                type="error"
                title="Ошибка загрузки"
                :text="error"
                class="mb-0"
              ></v-alert>
            </div>
            
            <v-card-text v-else class="pt-4">
              <v-form ref="enrollmentForm" v-model="formValid" @submit.prevent="saveEnrollment">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="enrollmentForm.group_id"
                      :items="groups"
                      item-title="groupname"
                      item-value="id"
                      label="Группа"
                      :rules="[v => !!v || 'Выберите группу']"
                      required
                      return-object
                    ></v-select>
                  </v-col>
                  
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="enrollmentForm.course_id"
                      :items="courses"
                      item-title="title"
                      item-value="id"
                      label="Курс"
                      :rules="[v => !!v || 'Выберите курс']"
                      required
                      return-object
                    ></v-select>
                  </v-col>
                  
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="enrollmentForm.teacher"
                      :items="instructors"
                      item-title="fio"
                      item-value="fio"
                      label="Инструктор"
                      :rules="[v => !!v || 'Выберите инструктора']"
                      required
                    ></v-select>
                  </v-col>
                  
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="enrollmentForm.typeOfLesson"
                      :items="lessonTypes"
                      item-title="title"
                      item-value="value"
                      label="Тип обучения"
                      :rules="[v => !!v || 'Выберите тип обучения']"
                      required
                    ></v-select>
                  </v-col>
                  
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="enrollmentForm.study_from"
                      label="Дата начала"
                      type="date"
                      :rules="[v => !!v || 'Выберите дату начала']"
                      required
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="enrollmentForm.study_to"
                      label="Дата окончания"
                      type="date"
                      :rules="[v => !!v || 'Выберите дату окончания']"
                      required
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12">
                    <v-select
                      v-model="enrollmentForm.status"
                      :items="statusOptions"
                      item-title="title"
                      item-value="value"
                      label="Статус"
                      :rules="[v => !!v || 'Выберите статус']"
                      required
                    ></v-select>
                  </v-col>
                </v-row>
                
                <v-divider class="my-4"></v-divider>
                
                <v-row>
                  <v-col cols="12" class="d-flex justify-end">
                    <v-btn
                      color="grey"
                      variant="text"
                      class="me-2"
                      :to="{ name: 'group-courses' }"
                    >
                      Отмена
                    </v-btn>
                    <v-btn
                      color="primary"
                      type="submit"
                      :loading="saving"
                    >
                      Сохранить изменения
                    </v-btn>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Snackbar для уведомлений -->
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        :timeout="3000"
      >
        {{ snackbar.text }}
      </v-snackbar>
    </v-container>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useGroupStore } from '@/stores/groupStore';
import { useCourseStore } from '@/stores/courseStore';
import { useEnrollmentStore } from '@/stores/enrollmentStore';
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
  components: {
    AppLayout
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
    const enrollmentId = route.params.id;
    
    const authStore = useAuthStore();
    const groupStore = useGroupStore();
    const courseStore = useCourseStore();
    const enrollmentStore = useEnrollmentStore();
    
    // Состояние
    const groups = ref([]);
    const courses = ref([]);
    const instructors = ref([]);
    const loading = ref(true);
    const saving = ref(false);
    const error = ref(null);
    const formValid = ref(false);
    const enrollmentForm = ref({
      id: null,
      group_id: null,
      course_id: null,
      teacher: '',
      typeOfLesson: '',
      study_from: '',
      study_to: '',
      status: 'active'
    });
    const snackbar = ref({
      show: false,
      color: 'success',
      text: ''
    });
    
    // Опции для полей формы
    const lessonTypes = [
      { title: 'Основной курс', value: 'Основной курс' },
      { title: 'Дополнительный курс', value: 'Дополнительный курс' },
      { title: 'Практические занятия', value: 'Практические занятия' },
      { title: 'Теоретические занятия', value: 'Теоретические занятия' },
      { title: 'Экзамен', value: 'Экзамен' }
    ];
    
    const statusOptions = [
      { title: 'Активна', value: 'active' },
      { title: 'Завершена', value: 'completed' },
      { title: 'Приостановлена', value: 'paused' },
      { title: 'Отменена', value: 'canceled' }
    ];
    
    // Методы
    const showNotification = (text, color = 'success') => {
      snackbar.value = {
        show: true,
        color,
        text
      };
    };
    
    const loadEnrollment = async () => {
      try {
        const enrollment = await enrollmentStore.getEnrollmentById(enrollmentId);
        
        if (!enrollment) {
          error.value = 'Запись не найдена';
          return;
        }
        
        // Поиск объектов группы и курса
        const group = groups.value.find(g => g.id === enrollment.group_id);
        const course = courses.value.find(c => c.id === enrollment.course_id);
        
        // Заполнение формы
        enrollmentForm.value = {
          id: enrollment.id,
          group_id: group,
          course_id: course,
          teacher: enrollment.teacher,
          typeOfLesson: enrollment.typeOfLesson,
          study_from: enrollment.study_from,
          study_to: enrollment.study_to,
          status: enrollment.status || 'active'
        };
      } catch (err) {
        console.error('Error loading enrollment:', err);
        error.value = err.message || 'Ошибка при загрузке данных';
      }
    };
    
    const fetchGroups = async () => {
      try {
        await groupStore.fetchGroups();
        groups.value = groupStore.getGroups;
      } catch (err) {
        console.error('Error fetching groups:', err);
        error.value = err.message || 'Ошибка при загрузке групп';
      }
    };
    
    const fetchCourses = async () => {
      try {
        await courseStore.fetchCourses();
        courses.value = courseStore.getCourses;
      } catch (err) {
        console.error('Error fetching courses:', err);
        error.value = err.message || 'Ошибка при загрузке курсов';
      }
    };
    
    const fetchInstructors = async () => {
      try {
        const response = await authStore.getApi().post('/user/list');
        instructors.value = (response.data || []).filter(user => 
          user.role === 'Инструктор' || 
          user.role === 'Преподаватель' || 
          user.role === 'Teacher'
        );
      } catch (err) {
        console.error('Error fetching instructors:', err);
        error.value = err.message || 'Ошибка при загрузке списка инструкторов';
      }
    };
    
    const saveEnrollment = async () => {
      saving.value = true;
      
      try {
        // Получение category_id из выбранного курса
        let categoryId = null;
        if (enrollmentForm.value.course_id && typeof enrollmentForm.value.course_id === 'object') {
          categoryId = enrollmentForm.value.course_id.category_id;
        }
        
        const payload = {
          id: enrollmentForm.value.id,
          group_id: enrollmentForm.value.group_id.id,
          course_id: enrollmentForm.value.course_id.id,
          category_id: categoryId,
          teacher: enrollmentForm.value.teacher,
          typeOfLesson: enrollmentForm.value.typeOfLesson,
          study_from: enrollmentForm.value.study_from,
          study_to: enrollmentForm.value.study_to,
          status: enrollmentForm.value.status
        };
        
        await enrollmentStore.updateEnrollment(payload);
        
        showNotification('Изменения успешно сохранены');
        
        // Перенаправление обратно к списку
        setTimeout(() => {
          router.push({ name: 'group-courses' });
        }, 1500);
      } catch (err) {
        console.error('Error updating enrollment:', err);
        showNotification(err.message || 'Ошибка при сохранении данных', 'error');
      } finally {
        saving.value = false;
      }
    };
    
    // Инициализация
    onMounted(async () => {
      try {
        // Параллельная загрузка данных
        await Promise.all([
          fetchGroups(),
          fetchCourses(),
          fetchInstructors(),
          enrollmentStore.fetchEnrollments()
        ]);
        
        // Загрузка данных о конкретной записи
        await loadEnrollment();
      } catch (err) {
        console.error('Error during initialization:', err);
        error.value = err.message || 'Ошибка при загрузке данных';
      } finally {
        loading.value = false;
      }
    });
    
    return {
      enrollmentId,
      groups,
      courses,
      instructors,
      loading,
      saving,
      error,
      formValid,
      enrollmentForm,
      snackbar,
      lessonTypes,
      statusOptions,
      saveEnrollment,
      showNotification
    };
  }
};
</script> 