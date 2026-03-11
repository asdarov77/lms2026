<template>
  <v-container fluid class="import-courses-container">
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 mb-4">Импорт курсов</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="bg-primary text-white">
            <v-icon class="mr-2">mdi-airplane</v-icon>
            Классы (воздушные суда)
          </v-card-title>
          
          <v-card-text>
            <v-row class="mb-2">
              <v-col cols="6">
                <h3 class="text-subtitle-1">В базе данных:</h3>
                <v-list density="compact" v-if="dbAircrafts.length">
                  <v-list-item
                    v-for="aircraft in dbAircrafts"
                    :key="aircraft.id"
                  >
                    <v-list-item-title>{{ aircraft.path }}</v-list-item-title>
                    <v-list-item-subtitle>
                      Курсов: {{ aircraft.courses?.length || 0 }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
                <v-alert v-else type="info" density="compact" class="mt-2">
                  Нет классов в базе данных
                </v-alert>
              </v-col>
              <v-col cols="6">
                <h3 class="text-subtitle-1">В хранилище:</h3>
                <v-list density="compact" v-if="fsAircrafts.length">
                  <v-list-item
                    v-for="aircraft in fsAircrafts"
                    :key="aircraft"
                    :class="{ 'bg-grey-lighten-3': selectedFsAircraft === aircraft }"
                    @click="selectFsAircraft(aircraft)"
                  >
                    <v-list-item-title>{{ aircraft }}</v-list-item-title>
                    <template v-slot:append>
                      <v-btn
                        v-if="!aircraftExistsInDb(aircraft)"
                        icon="mdi-plus"
                        size="small"
                        color="success"
                        @click.stop="importClass(aircraft)"
                        :loading="importingClass === aircraft"
                      ></v-btn>
                      <v-chip v-else size="x-small" color="warning">в базе</v-chip>
                    </template>
                  </v-list-item>
                </v-list>
                <v-alert v-else type="info" density="compact" class="mt-2">
                  Нет классов в хранилище
                </v-alert>
              </v-col>
            </v-row>
            
            <v-btn
              color="primary"
              class="mt-2"
              block
              @click="refreshAll"
              :loading="loading"
            >
              <v-icon start>mdi-refresh</v-icon>
              Обновить список
            </v-btn>
            
            <v-divider class="my-4"></v-divider>
            
            <v-btn
              color="error"
              block
              @click="confirmClearDatabase"
              :loading="clearingDb"
            >
              <v-icon start>mdi-delete-outline</v-icon>
              Очистить базу данных
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="bg-primary text-white">
            <v-icon class="mr-2">mdi-cog</v-icon>
            Настройки импорта курсов
          </v-card-title>
          
          <v-card-text>
            <v-select
              v-model="selectedDbAircraft"
              :items="dbAircrafts"
              item-title="path"
              item-value="id"
              label="Выберите класс для импорта курсов"
              return-object
              density="compact"
              variant="outlined"
              class="mb-4"
            ></v-select>
            
            <v-switch
              v-model="clearDb"
              label="Очистить базу данных перед импортом"
              color="error"
              hint="Удаляет все существующие курсы, категории и вопросы"
              persistent-hint
            ></v-switch>
            
            <v-divider class="my-4"></v-divider>
            
            <div v-if="selectedDbAircraft" class="text-center">
              <p class="text-h6 mb-2">Выбран класс: {{ selectedDbAircraft.path }}</p>
              <v-btn
                color="success"
                size="large"
                @click="startImport"
                :loading="importing"
                :disabled="!selectedDbAircraft"
              >
                <v-icon start>mdi-import</v-icon>
                Запустить импорт курсов
              </v-btn>
            </div>
            
            <v-alert v-else type="info" class="mt-4">
              Выберите класс для импорта курсов из списка слева
            </v-alert>
          </v-card-text>
        </v-card>
        
        <v-card v-if="importResult" class="mt-4">
          <v-card-title :class="importResult.success ? 'bg-success' : 'bg-error'">
            <v-icon class="mr-2">{{ importResult.success ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            Результат импорта
          </v-card-title>
          
          <v-card-text>
            <v-list density="compact">
              <v-list-item>
                <v-list-item-title>Курсов создано:</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.courses_created || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Категорий:</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.categories_created || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Структур создано:</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.aukstructures_created || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Вопросов импортировано:</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.questions_imported || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Ответов импортировано:</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.answers_imported || 0 }}</v-chip>
                </template>
              </v-list-item>
            </v-list>
            
            <v-alert
              v-if="importResult.result?.errors?.length"
              type="warning"
              class="mt-2"
            >
              <div class="text-subtitle-1">Ошибки:</div>
              <ul>
                <li v-for="(error, idx) in importResult.result.errors.slice(0, 5)" :key="idx">
                  {{ error }}
                </li>
              </ul>
              <div v-if="importResult.result.errors.length > 5">
                ... и ещё {{ importResult.result.errors.length - 5 }} ошибок
              </div>
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { useCourseStore } from '@/stores/courseStore';

export default {
  name: 'ImportCourses',
  
  data() {
    return {
      loading: false,
      clearingDb: false,
      importing: false,
      importingClass: null,
      fsAircrafts: [],
      dbAircrafts: [],
      selectedFsAircraft: null,
      selectedDbAircraft: null,
      clearDb: false,
      importResult: null
    };
  },
  
  mounted() {
    this.refreshAll();
  },
  
  methods: {
    async refreshAll() {
      await Promise.all([
        this.refreshFsAircrafts(),
        this.refreshDbAircrafts()
      ]);
    },
    
    async refreshFsAircrafts() {
      this.loading = true;
      try {
        const courseStore = useCourseStore();
        const aircrafts = await courseStore.getImportAircrafts();
        this.fsAircrafts = aircrafts.map(a => a.path);
      } catch (err) {
        console.error('Ошибка загрузки классов из хранилища:', err);
      } finally {
        this.loading = false;
      }
    },
    
    async refreshDbAircrafts() {
      try {
        const courseStore = useCourseStore();
        await courseStore.fetchAircrafts();
        this.dbAircrafts = courseStore.aircrafts;
      } catch (err) {
        console.error('Ошибка загрузки классов из БД:', err);
      }
    },
    
    selectFsAircraft(aircraft) {
      this.selectedFsAircraft = aircraft;
    },
    
    aircraftExistsInDb(path) {
      return this.dbAircrafts.some(a => a.path === path);
    },
    
    async importClass(path) {
      this.importingClass = path;
      
      try {
        const courseStore = useCourseStore();
        const authStore = courseStore.$pinia._s.get('auth');
        const api = authStore.getApi();
        await api.post('/classes', {
          path: path,
          title: path
        });
          this.$root.$emit('show-snackbar', {
            message: `Класс "${path}" успешно добавлен в базу данных`,
            color: 'success'
          });
          await this.refreshDbAircrafts();
      } catch (err) {
        console.error('Ошибка импорта класса:', err);
        this.$root.$emit('show-snackbar', {
          message: 'Ошибка импорта класса: ' + err.message,
          color: 'error'
        });
      } finally {
        this.importingClass = null;
      }
    },
    
    async startImport() {
      if (!this.selectedDbAircraft) return;
      
      this.importing = true;
      this.importResult = null;
      
      try {
        const courseStore = useCourseStore();
        const result = await courseStore.importCourse({
          aircraft_path: this.selectedDbAircraft.path,
          force: this.clearDb
        });

        this.importResult = result;

        if (result?.success) {
          this.$root.$emit('show-snackbar', {
            message: 'Импорт курсов завершен успешно',
            color: 'success'
          });
        } else {
          this.$root.$emit('show-snackbar', {
            message: 'Ошибка импорта: ' + (result?.message || 'Неизвестная ошибка'),
            color: 'error'
          });
        }
      } catch (err) {
        console.error('Ошибка импорта:', err);
        this.$root.$emit('show-snackbar', {
          message: 'Ошибка импорта: ' + err.message,
          color: 'error'
        });
      } finally {
        this.importing = false;
      }
    },
    
    confirmClearDatabase() {
      if (confirm('Вы уверены, что хотите очистить базу данных? Все курсы, категории, вопросы и ответы будут удалены!')) {
        this.clearDatabase();
      }
    },
    
    async clearDatabase() {
      this.clearingDb = true;
      
      try {
        const courseStore = useCourseStore();
        const authStore = courseStore.$pinia._s.get('auth');
        const api = authStore.getApi();
        await api.post('/import/clear');
          this.$root.$emit('show-snackbar', {
            message: 'База данных успешно очищена',
            color: 'success'
          });
          await this.refreshAll();
      } catch (err) {
        console.error('Ошибка очистки базы данных:', err);
        this.$root.$emit('show-snackbar', {
          message: 'Ошибка очистки базы данных: ' + err.message,
          color: 'error'
        });
      } finally {
        this.clearingDb = false;
      }
    }
  }
};
</script>

<style scoped>
.import-courses-container {
  padding: 20px;
}
</style>
