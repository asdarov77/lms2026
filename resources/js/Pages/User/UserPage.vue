<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="topics"
      :loading="loading"
      class="elevation-1"
    >
      <template v-slot:item="{ item: topic }">
        <tr>
          <td>{{ topic.study_from }}-{{ topic.study_to }}</td>
          <td>{{ getGroupName(topic.group_id) }}</td>
          <td>{{ getCourseName(topic.course_id) }}</td>
          <td>{{ getCategoryName(topic.category_id) }}</td>
          <td>{{ topic.teacher }}</td>
          <td>{{ topic.typeOfLesson }}</td>
          <td>
            <v-btn 
              color="success" 
              flat 
              :to="{
                name: 'courses.itemmani',
                query: { idEdit: topic.course_id, idCategory: topic.category_id },
              }" 
              target="_blank"
            >
              Открыть
            </v-btn>
          </td>
          <td>
            <v-btn 
              color="success" 
              flat 
              :to="{
                name: 'questions',
                query: { idEdit: topic.course_id, idCategory: topic.category_id },
              }" 
              target="_blank"
            >
              Тесты
            </v-btn>
          </td>
          <td align="center">
            <v-icon 
              small 
              color="green" 
              class="mr-2" 
              @click="editItem(topic)"
            >
              mdi-pencil
            </v-icon>
            <v-icon 
              small 
              @click="confirmDelete(topic)" 
              color="red"
            >
              mdi-delete
            </v-icon>
          </td>
        </tr>
      </template>
    </v-data-table>

    <!-- Edit Dialog -->
    <v-dialog v-model="dialog.edit" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="text-h5">Редактирование записи</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.study_from"
                  label="Начало периода"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.study_to"
                  label="Конец периода"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="editedItem.group_id"
                  :items="groups"
                  item-title="groupname"
                  item-value="id"
                  label="Группа"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="editedItem.course_id"
                  :items="courses"
                  item-title="title"
                  item-value="id"
                  label="Курс"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="editedItem.category_id"
                  :items="categories"
                  item-title="title"
                  item-value="id"
                  label="Категория"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.teacher"
                  label="Преподаватель"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.typeOfLesson"
                  label="Тип занятия"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" variant="text" @click="closeDialog">Отмена</v-btn>
          <v-btn color="blue-darken-1" variant="text" @click="saveItem">Сохранить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="dialog.delete" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Вы уверены, что хотите удалить этот элемент?</v-card-title>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" variant="text" @click="closeDialog">Отмена</v-btn>
          <v-btn color="red-darken-1" variant="text" @click="deleteItemConfirm">Удалить</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for notifications -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">Закрыть</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import $api from "../../api/httpClient"

const apiUrl = import.meta.env.VITE_APP_URL
const authStore = useAuthStore()

const loading = ref(false)
const topics = ref([])
const groups = ref([])
const courses = ref([])
const categories = ref([])

const defaultItem = {
  id: null,
  study_from: '',
  study_to: '',
  group_id: null,
  course_id: null,
  category_id: null,
  teacher: '',
  typeOfLesson: '',
}

const editedItem = reactive({...defaultItem})
const dialog = reactive({
  edit: false,
  delete: false
})
const snackbar = reactive({
  show: false,
  text: '',
  color: 'success'
})

const headers = [
  { title: 'Период', key: 'period' },
  { title: 'Группа', key: 'group' },
  { title: 'Курс', key: 'course' },
  { title: 'Категория', key: 'category' },
  { title: 'Преподаватель', key: 'teacher' },
  { title: 'Тип занятия', key: 'typeOfLesson' },
  { title: 'Курс', key: 'open_course', sortable: false },
  { title: 'Тесты', key: 'tests', sortable: false },
  { title: 'Действия', key: 'actions', sortable: false },
]

const getGroupName = (groupId) => {
  if (!groupId) return 'Не указано'
  const group = groups.value.find(g => g.id === groupId)
  return group ? group.groupname : `Группа ${groupId}`
}

const getCourseName = (courseId) => {
  if (!courseId) return 'Не указано'
  const course = courses.value.find(c => c.id === courseId)
  return course ? course.title : `Курс ${courseId}`
}

const getCategoryName = (categoryId) => {
  if (!categoryId) return 'Не указано'
  const category = categories.value.find(c => c.id === categoryId)
  return category ? category.title : `Категория ${categoryId}`
}

const editItem = (item) => {
  // Clone the item to avoid modifying the original directly
  Object.assign(editedItem, {...item})
  dialog.edit = true
}

const confirmDelete = (item) => {
  Object.assign(editedItem, {...item})
  dialog.delete = true
}

const closeDialog = () => {
  dialog.edit = false
  dialog.delete = false
  // Reset form after dialog closes
  setTimeout(() => {
    Object.assign(editedItem, {...defaultItem})
  }, 300)
}

const saveItem = async () => {
  try {
    loading.value = true
    // Update the item via API
    const response = await $api.put(`/api/topics/${editedItem.id}`, editedItem)
    
    // Update local data
    const index = topics.value.findIndex(item => item.id === editedItem.id)
    if (index !== -1) {
      Object.assign(topics.value[index], response.data)
    }
    
    // Show success message
    snackbar.color = 'success'
    snackbar.text = 'Элемент успешно обновлен'
    snackbar.show = true
    
    closeDialog()
  } catch (error) {
    console.error('Error updating item:', error)
    snackbar.color = 'error'
    snackbar.text = 'Ошибка при обновлении элемента'
    snackbar.show = true
  } finally {
    loading.value = false
  }
}

const deleteItemConfirm = async () => {
  try {
    loading.value = true
    // Delete the item via API
    await $api.delete(`/api/topics/${editedItem.id}`)
    
    // Remove from local data
    topics.value = topics.value.filter(item => item.id !== editedItem.id)
    
    // Show success message
    snackbar.color = 'success'
    snackbar.text = 'Элемент успешно удален'
    snackbar.show = true
    
    closeDialog()
  } catch (error) {
    console.error('Error deleting item:', error)
    snackbar.color = 'error'
    snackbar.text = 'Ошибка при удалении элемента'
    snackbar.show = true
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    loading.value = true
    // Fetch all necessary data in parallel
    const [topicsResponse, groupsResponse, coursesResponse, categoriesResponse] = await Promise.all([
      $api.get('/api/topics'),
      $api.get('/api/groups'),
      $api.get('/api/courses'),
      $api.get('/api/categories')
    ])
    
    topics.value = topicsResponse.data
    groups.value = groupsResponse.data
    courses.value = coursesResponse.data
    categories.value = categoriesResponse.data
  } catch (error) {
    console.error('Error fetching data:', error)
    snackbar.color = 'error'
    snackbar.text = 'Ошибка при загрузке данных'
    snackbar.show = true
  } finally {
    loading.value = false
  }
})
</script>

<style>
@import "../../../css/app.css";

.center-text {
  text-align: center;
}

.bg-lblue {
  background-color: #a4ccff;
}

.green-100 {
  background-color: #50c950;
}

.green-70 {
  background-color: rgba(143, 215, 139, 0.94);
}

.yellow-50 {
  background-color: rgba(236, 236, 155, 0.87);
}

.red-50 {
  background-color: rgba(236, 201, 155, 0.87);
}
</style>


