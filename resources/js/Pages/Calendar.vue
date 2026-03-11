<template>
  <v-container fluid>
    <!-- Header Section -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <h1 class="text-h4 font-weight-bold primary--text">Календарь</h1>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end">
        <v-btn
          color="primary"
          @click="openAddEventDialog"
          prepend-icon="mdi-plus"
        >
          Добавить событие
        </v-btn>
      </v-col>
    </v-row>

    <!-- Calendar View -->
    <v-card>
      <v-card-text>
        <v-row>
          <v-col cols="12" class="d-flex align-center mb-4">
            <v-btn
              icon
              size="small"
              @click="previousMonth"
              class="mr-2"
            >
              <v-icon>mdi-chevron-left</v-icon>
            </v-btn>
            <h2 class="text-h5">
              {{ currentMonthName }} {{ currentYear }}
            </h2>
            <v-btn
              icon
              size="small"
              @click="nextMonth"
              class="ml-2"
            >
              <v-icon>mdi-chevron-right</v-icon>
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              variant="text"
              @click="today"
            >
              Сегодня
            </v-btn>
          </v-col>
        </v-row>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
          <!-- Weekday Headers -->
          <div class="calendar-header">
            <div
              v-for="day in weekDays"
              :key="day"
              class="calendar-cell header"
            >
              {{ day }}
            </div>
          </div>

          <!-- Calendar Days -->
          <div class="calendar-body">
            <div
              v-for="(week, weekIndex) in calendarDays"
              :key="weekIndex"
              class="calendar-row"
            >
              <div
                v-for="day in week"
                :key="day.date"
                :class="[
                  'calendar-cell',
                  {
                    'other-month': !day.currentMonth,
                    'today': day.isToday,
                    'has-events': day.events.length > 0
                  }
                ]"
                @click="selectDate(day)"
              >
                <div class="day-number">{{ day.dayNumber }}</div>
                <div class="events-preview">
                  <div
                    v-for="event in day.events.slice(0, 2)"
                    :key="event.id"
                    class="event-dot"
                    :style="{ backgroundColor: event.color }"
                  ></div>
                  <div
                    v-if="day.events.length > 2"
                    class="more-events"
                  >
                    +{{ day.events.length - 2 }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </v-card-text>
    </v-card>

    <!-- Add/Edit Event Dialog -->
    <v-dialog v-model="eventDialog" max-width="600px">
      <v-card>
        <v-card-title class="text-h5">
          {{ isEditing ? 'Редактировать событие' : 'Добавить событие' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="eventForm" v-model="formValid">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="eventForm.title"
                  label="Название события"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="eventForm.description"
                  label="Описание"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-textarea>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="eventForm.startDate"
                  label="Дата начала"
                  type="date"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="eventForm.endDate"
                  label="Дата окончания"
                  type="date"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-select
                  v-model="eventForm.type"
                  :items="eventTypes"
                  label="Тип события"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-select>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="eventDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="primary"
            variant="text"
            @click="saveEvent"
            :loading="saving"
            :disabled="!formValid"
          >
            Сохранить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Event Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="600px">
      <v-card>
        <v-card-title class="text-h5">
          {{ selectedEvent?.title }}
        </v-card-title>
        <v-card-text>
          <div class="text-body-1 mb-4">
            {{ selectedEvent?.description }}
          </div>
          <div class="d-flex align-center mb-2">
            <v-icon size="small" color="primary" class="mr-1">
              mdi-calendar
            </v-icon>
            <span>
              {{ formatDate(selectedEvent?.startDate) }} - {{ formatDate(selectedEvent?.endDate) }}
            </span>
          </div>
          <div class="d-flex align-center">
            <v-icon size="small" color="primary" class="mr-1">
              mdi-tag
            </v-icon>
            <span>{{ selectedEvent?.type }}</span>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="text"
            @click="editEvent"
          >
            Редактировать
          </v-btn>
          <v-btn
            color="error"
            variant="text"
            @click="deleteEvent"
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      :timeout="3000"
    >
      {{ snackbarText }}
    </v-snackbar>
  </v-container>
</template>

<script>
export default {
  name: 'Calendar',

  data() {
    return {
      currentDate: new Date(),
      weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
      eventDialog: false,
      detailsDialog: false,
      isEditing: false,
      formValid: false,
      saving: false,
      selectedEvent: null,
      eventForm: {
        title: '',
        description: '',
        startDate: '',
        endDate: '',
        type: ''
      },
      eventTypes: [
        'Лекция',
        'Практика',
        'Консультация',
        'Экзамен',
        'Другое'
      ],
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success'
    }
  },

  computed: {
    currentMonthName() {
      return this.currentDate.toLocaleString('ru-RU', { month: 'long' })
    },

    currentYear() {
      return this.currentDate.getFullYear()
    },

    calendarDays() {
      const year = this.currentDate.getFullYear()
      const month = this.currentDate.getMonth()
      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)
      const startingDay = firstDay.getDay() || 7
      const totalDays = lastDay.getDate()
      const totalWeeks = Math.ceil((totalDays + startingDay - 1) / 7)

      const days = []
      const today = new Date()

      // Add days from previous month
      const prevMonthLastDay = new Date(year, month, 0).getDate()
      for (let i = startingDay - 1; i > 0; i--) {
        days.push({
          date: new Date(year, month - 1, prevMonthLastDay - i + 1),
          dayNumber: prevMonthLastDay - i + 1,
          currentMonth: false,
          isToday: false,
          events: []
        })
      }

      // Add days from current month
      for (let i = 1; i <= totalDays; i++) {
        const date = new Date(year, month, i)
        days.push({
          date,
          dayNumber: i,
          currentMonth: true,
          isToday: this.isSameDay(date, today),
          events: this.getEventsForDate(date)
        })
      }

      // Add days from next month
      const remainingDays = 7 - (days.length % 7)
      if (remainingDays < 7) {
        for (let i = 1; i <= remainingDays; i++) {
          days.push({
            date: new Date(year, month + 1, i),
            dayNumber: i,
            currentMonth: false,
            isToday: false,
            events: []
          })
        }
      }

      // Group days into weeks
      const weeks = []
      for (let i = 0; i < days.length; i += 7) {
        weeks.push(days.slice(i, i + 7))
      }

      return weeks
    }
  },

  methods: {
    previousMonth() {
      this.currentDate = new Date(
        this.currentDate.getFullYear(),
        this.currentDate.getMonth() - 1
      )
    },

    nextMonth() {
      this.currentDate = new Date(
        this.currentDate.getFullYear(),
        this.currentDate.getMonth() + 1
      )
    },

    today() {
      this.currentDate = new Date()
    },

    selectDate(day) {
      if (!day.currentMonth) return
      this.selectedEvent = null
      this.eventForm = {
        title: '',
        description: '',
        startDate: this.formatDateForInput(day.date),
        endDate: this.formatDateForInput(day.date),
        type: ''
      }
      this.isEditing = false
      this.eventDialog = true
    },

    openAddEventDialog() {
      this.selectedEvent = null
      this.eventForm = {
        title: '',
        description: '',
        startDate: this.formatDateForInput(new Date()),
        endDate: this.formatDateForInput(new Date()),
        type: ''
      }
      this.isEditing = false
      this.eventDialog = true
    },

    editEvent() {
      this.eventForm = { ...this.selectedEvent }
      this.isEditing = true
      this.detailsDialog = false
      this.eventDialog = true
    },

    async saveEvent() {
      if (!this.$refs.eventForm.validate()) return

      this.saving = true
      try {
        if (this.isEditing) {
          await this.$store.dispatch('Calendar/updateEvent', {
            id: this.selectedEvent.id,
            ...this.eventForm
          })
        } else {
          await this.$store.dispatch('Calendar/createEvent', this.eventForm)
        }
        this.eventDialog = false
        this.showNotification(
          this.isEditing ? 'Событие обновлено' : 'Событие добавлено',
          'success'
        )
      } catch (error) {
        this.showNotification('Ошибка при сохранении события', 'error')
      } finally {
        this.saving = false
      }
    },

    async deleteEvent() {
      try {
        await this.$store.dispatch('Calendar/deleteEvent', this.selectedEvent.id)
        this.detailsDialog = false
        this.showNotification('Событие удалено', 'success')
      } catch (error) {
        this.showNotification('Ошибка при удалении события', 'error')
      }
    },

    getEventsForDate(date) {
      // Implement event fetching logic
      return []
    },

    isSameDay(date1, date2) {
      return (
        date1.getDate() === date2.getDate() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getFullYear() === date2.getFullYear()
      )
    },

    formatDate(date) {
      return date.toLocaleDateString('ru-RU')
    },

    formatDateForInput(date) {
      return date.toISOString().split('T')[0]
    },

    showNotification(text, color = 'success') {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    }
  }
}
</script>

<style scoped>
.calendar-grid {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.calendar-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background-color: #f5f5f5;
  border-bottom: 1px solid #e0e0e0;
}

.calendar-cell {
  padding: 8px;
  min-height: 100px;
  border-right: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
}

.calendar-cell.header {
  text-align: center;
  font-weight: 500;
  padding: 12px 8px;
  background-color: #f5f5f5;
}

.calendar-cell.other-month {
  background-color: #fafafa;
  color: #9e9e9e;
}

.calendar-cell.today {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.day-number {
  font-weight: 500;
  margin-bottom: 4px;
}

.events-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.event-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.more-events {
  font-size: 0.75rem;
  color: #9e9e9e;
  margin-left: 4px;
}

.v-card {
  border-radius: 8px;
}
</style>
  