import { format, formatDistance } from 'date-fns';
import { ru } from 'date-fns/locale';

/**
 * Format a date using date-fns
 * @param {Date|string|number} date - The date to format
 * @param {string} formatString - The format string
 * @returns {string} The formatted date
 */
export function formatDate(date, formatString = 'dd.MM.yyyy HH:mm') {
  if (!date) return '';
  return format(new Date(date), formatString, { locale: ru });
}

/**
 * Format a relative time (e.g., "5 minutes ago")
 * @param {Date|string|number} date - The date to format
 * @param {Date} baseDate - The base date to compare with (default: now)
 * @returns {string} The relative time
 */
export function formatRelativeTime(date, baseDate = new Date()) {
  if (!date) return '';
  return formatDistance(new Date(date), baseDate, { 
    addSuffix: true,
    locale: ru
  });
}

/**
 * Get a notification time in readable format
 * @param {Date|string|number} time - The time to format
 * @returns {string} The formatted time
 */
export function formatNotificationTime(time) {
  if (!time) return '';
  const date = new Date(time);
  const now = new Date();
  const diffInHours = (now - date) / (1000 * 60 * 60);
  
  if (diffInHours < 24) {
    return formatRelativeTime(date, now);
  } else {
    return formatDate(date, 'dd MMM, HH:mm');
  }
} 