import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import { ref } from 'vue';

const customLightTheme = {
    dark: false,
    colors: {
        primary: '#3F51B5',
        secondary: '#5C6BC0',
        accent: '#8C9EFF',
        error: '#FF5252',
        info: '#2196F3',
        success: '#4CAF50',
        warning: '#FFC107',
        background: '#F5F5F5',
        surface: '#FFFFFF',
    },
};

const customDarkTheme = {
    dark: true,
    colors: {
        primary: '#5C6BC0',
        secondary: '#7986CB',
        accent: '#9FA8DA',
        error: '#FF5252',
        info: '#2196F3',
        success: '#4CAF50',
        warning: '#FFC107',
        background: '#121212',
        surface: '#212121',
    },
};

const userFormKey = ref(0);

export default createVuetify({
    theme: {
        defaultTheme: 'customLightTheme',
        themes: {
            customLightTheme,
            customDarkTheme,
        },
    },
    defaults: {
        VBtn: {
            variant: 'flat',
            rounded: true,
        },
        VCard: {
            rounded: 'lg',
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VCheckbox: {
            color: 'primary',
        },
        VRadio: {
            color: 'primary',
        },
        VSwitch: {
            color: 'primary',
        },
        VChip: {
            rounded: true,
        },
    },
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
});