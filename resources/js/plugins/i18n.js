import { createI18n } from 'vue-i18n'
import enMessages from '../locales/en.json'
import ruMessages from '../locales/ru.json'

const messages = {
  en: enMessages,
  ru: ruMessages,
}

export default createI18n({
  legacy: false,
  locale: 'ru',
  fallbackLocale: 'en',
  messages,
}) 