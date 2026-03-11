<template>
  <v-container fluid class="messages-container">
    <v-row>
      <!-- Contacts List -->
      <v-col cols="12" md="4" class="contacts-column">
        <v-card class="h-100">
          <v-card-title class="text-h6">
            Сообщения
          </v-card-title>
          <v-card-text class="pa-0">
            <v-list>
              <v-list-item
                v-for="contact in contacts"
                :key="contact.id"
                :class="{ 'active': selectedContact?.id === contact.id }"
                @click="selectContact(contact)"
              >
                <template v-slot:prepend>
                  <v-avatar
                    :color="contact.color"
                    size="40"
                    class="mr-2"
                  >
                    <v-img
                      v-if="contact.avatar"
                      :src="contact.avatar"
                      alt="Avatar"
                    ></v-img>
                    <span v-else class="text-h6 white--text">
                      {{ contact.name.charAt(0) }}
                    </span>
                  </v-avatar>
                </template>

                <v-list-item-title class="text-subtitle-1">
                  {{ contact.name }}
                </v-list-item-title>

                <v-list-item-subtitle class="text-caption">
                  {{ contact.lastMessage }}
                </v-list-item-subtitle>

                <template v-slot:append>
                  <div class="d-flex flex-column align-end">
                    <span class="text-caption grey--text">
                      {{ formatTime(contact.lastMessageTime) }}
                    </span>
                    <v-chip
                      v-if="contact.unreadCount"
                      color="primary"
                      size="small"
                      class="mt-1"
                    >
                      {{ contact.unreadCount }}
                    </v-chip>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Chat Area -->
      <v-col cols="12" md="8" class="chat-column">
        <v-card class="h-100 d-flex flex-column">
          <!-- Chat Header -->
          <v-card-title class="text-h6 d-flex align-center">
            <template v-if="selectedContact">
              <v-avatar
                :color="selectedContact.color"
                size="40"
                class="mr-2"
              >
                <v-img
                  v-if="selectedContact.avatar"
                  :src="selectedContact.avatar"
                  alt="Avatar"
                ></v-img>
                <span v-else class="text-h6 white--text">
                  {{ selectedContact.name.charAt(0) }}
                </span>
              </v-avatar>
              <div>
                <div>{{ selectedContact.name }}</div>
                <div class="text-caption grey--text">
                  {{ selectedContact.status }}
                </div>
              </div>
            </template>
            <template v-else>
              Выберите контакт для начала общения
            </template>
          </v-card-title>

          <!-- Messages List -->
          <v-card-text class="flex-grow-1 messages-list pa-4">
            <div
              v-for="message in messages"
              :key="message.id"
              :class="['message', message.isOutgoing ? 'outgoing' : 'incoming']"
            >
              <div class="message-content">
                {{ message.text }}
              </div>
              <div class="message-time text-caption">
                {{ formatTime(message.time) }}
              </div>
            </div>
          </v-card-text>

          <!-- Message Input -->
          <v-card-actions class="pa-4">
            <v-text-field
              v-model="newMessage"
              placeholder="Введите сообщение..."
              variant="outlined"
              density="compact"
              hide-details
              class="mr-2"
              @keyup.enter="sendMessage"
            ></v-text-field>
            <v-btn
              color="primary"
              icon
              @click="sendMessage"
              :disabled="!newMessage.trim()"
            >
              <v-icon>mdi-send</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'Messages',

  data() {
    return {
      selectedContact: null,
      newMessage: '',
      contacts: [
        {
          id: 1,
          name: 'Иван Петров',
          status: 'Онлайн',
          lastMessage: 'Отлично, спасибо!',
          lastMessageTime: '10:30',
          unreadCount: 2,
          color: 'primary',
          avatar: null
        },
        {
          id: 2,
          name: 'Анна Сидорова',
          status: 'Был(а) в сети 5 минут назад',
          lastMessage: 'Когда следующее занятие?',
          lastMessageTime: '09:15',
          unreadCount: 0,
          color: 'success',
          avatar: null
        },
        {
          id: 3,
          name: 'Максим Иванов',
          status: 'Оффлайн',
          lastMessage: 'Хорошо, я понял',
          lastMessageTime: 'Вчера',
          unreadCount: 1,
          color: 'warning',
          avatar: null
        }
      ],
      messages: [
        {
          id: 1,
          text: 'Привет! Как дела?',
          time: '10:25',
          isOutgoing: false
        },
        {
          id: 2,
          text: 'Привет! Всё отлично, спасибо!',
          time: '10:26',
          isOutgoing: true
        },
        {
          id: 3,
          text: 'Как продвигается обучение?',
          time: '10:28',
          isOutgoing: false
        },
        {
          id: 4,
          text: 'Очень хорошо, уже почти закончил курс!',
          time: '10:29',
          isOutgoing: true
        }
      ]
    }
  },

  methods: {
    selectContact(contact) {
      this.selectedContact = contact
      this.loadMessages(contact.id)
    },

    async loadMessages(contactId) {
      try {
        const response = await this.$store.dispatch('Messages/getMessages', contactId)
        this.messages = response
      } catch (error) {
        console.error('Error loading messages:', error)
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || !this.selectedContact) return

      try {
        await this.$store.dispatch('Messages/sendMessage', {
          contactId: this.selectedContact.id,
          text: this.newMessage
        })
        this.newMessage = ''
        this.loadMessages(this.selectedContact.id)
      } catch (error) {
        console.error('Error sending message:', error)
      }
    },

    formatTime(time) {
      return time
    }
  }
}
</script>

<style scoped>
.messages-container {
  height: calc(100vh - 64px);
}

.contacts-column,
.chat-column {
  height: 100%;
}

.messages-list {
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.message {
  max-width: 70%;
  padding: 12px;
  border-radius: 12px;
  position: relative;
}

.message.incoming {
  align-self: flex-start;
  background-color: #f5f5f5;
}

.message.outgoing {
  align-self: flex-end;
  background-color: var(--v-primary-base);
  color: white;
}

.message-content {
  margin-bottom: 4px;
}

.message-time {
  text-align: right;
  opacity: 0.7;
}

.v-list-item.active {
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.v-list-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}
</style> 