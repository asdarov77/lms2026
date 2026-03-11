import { faker } from '@faker-js/faker';

faker.seed(12345);

export interface TestUser {
  id?: number;
  email: string;
  password: string;
  fio: string;
  role?: string;
}

export interface TestCourse {
  id?: number;
  name: string;
  description: string;
  code?: string;
}

export interface TestGroup {
  id?: number;
  name: string;
  description?: string;
}

export interface TestCategory {
  id?: number;
  name: string;
  parent_id?: number;
}

const generateSeed = (): number => {
  return parseInt(process.env.FAKER_SEED || '12345');
};

faker.seed(generateSeed());

export const testData = {
  user(): TestUser {
    return {
      email: faker.internet.email().toLowerCase(),
      password: 'TestPassword123!',
      fio: faker.person.fullName(),
      role: 'student',
    };
  },

  admin(): TestUser {
    return {
      email: faker.internet.email().toLowerCase(),
      password: 'AdminPassword123!',
      fio: faker.person.fullName(),
      role: 'admin',
    };
  },

  course(): TestCourse {
    return {
      name: faker.lorem.words(3),
      description: faker.lorem.paragraph(),
      code: faker.string.alphanumeric(6).toUpperCase(),
    };
  },

  group(): TestGroup {
    return {
      name: faker.company.name() + ' Group',
      description: faker.lorem.sentence(),
    };
  },

  category(): TestCategory {
    return {
      name: faker.commerce.department(),
      parent_id: undefined,
    };
  },

  loginForm() {
    return {
      email: faker.internet.email(),
      password: faker.internet.password({ length: 8 }),
    };
  },

  registrationForm(existingEmails: string[] = []) {
    let email: string;
    do {
      email = faker.internet.email().toLowerCase();
    } while (existingEmails.includes(email));

    return {
      email,
      password: 'Password123!',
      fio: faker.person.fullName(),
      password_confirmation: 'Password123!',
    };
  },

  updateFields(base: Record<string, unknown>) {
    const fields = { ...base };
    Object.keys(fields).forEach(key => {
      if (typeof fields[key] === 'string') {
        fields[key] = faker.lorem.words(2);
      }
    });
    return fields;
  },
};

export const selectors = {
  buttons: {
    save: '[data-testid="btn-save"]',
    submit: '[data-testid="btn-submit"]',
    create: '[data-testid="btn-create"]',
    edit: '[data-testid="btn-edit"]',
    delete: '[data-testid="btn-delete"]',
    cancel: '[data-testid="btn-cancel"]',
    confirm: '[data-testid="btn-confirm"]',
    close: '[data-testid="btn-close"]',
  },
  
  forms: {
    input: 'input[data-testid]',
    select: 'select[data-testid]',
    textarea: 'textarea[data-testid]',
    checkbox: 'checkbox[data-testid]',
  },

  navigation: {
    link: 'a[data-testid]',
    menuItem: '[data-testid="nav-item"]',
  },

  messages: {
    toast: '.v-snackbar__wrapper',
    error: '[data-testid="error-message"]',
    success: '[data-testid="success-message"]',
  },

  tables: {
    row: 'tbody tr',
    cell: 'td',
    empty: '.v-data-table__tr--empty',
  },

  modal: {
    dialog: '.v-dialog',
    overlay: '.v-overlay__scrim',
  },
};
