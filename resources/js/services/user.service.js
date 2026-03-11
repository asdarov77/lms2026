const USER_KEY = 'user'

/**
 * Manage the how user data is being stored and retrieved from storage.
 *
 * Current implementation stores to localStorage. Local Storage should always be
 * accessed through this instance.
 **/
const UserService = {
  getUser() {
    const userData = localStorage.getItem(USER_KEY);
    console.log('Raw user data from localStorage:', userData);
    return userData;
  },

  saveUser(user) {
    try {
      // If user is already a string, don't stringify again
      const userData = typeof user === 'string' ? user : JSON.stringify(user);
      console.log('Saving user data to localStorage:', userData);
      localStorage.setItem(USER_KEY, userData);
    } catch (error) {
      console.error('Error saving user data:', error);
      localStorage.removeItem(USER_KEY);
    }
  },

  removeUser() {
    localStorage.removeItem(USER_KEY);
  }
}

export { UserService }
