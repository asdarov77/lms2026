import { createStore } from 'vuex';

export default createStore({
    state: {
        categories: [],
        categoryMap: {},
        courses: [],
        currentUser: null,
        groups: [],
        loading: false,
        error: null,
    },
    getters: {
        getCategoryById: (state) => (id) => {
            return state.categoryMap[id] || null;
        },
        getCategoryCodeById: (state) => (id) => {
            const category = state.categoryMap[id];
            return category ? category.categoryCode : null;
        },
    },
    mutations: {
        SET_CATEGORIES(state, categories) {
            state.categories = categories;
            state.categoryMap = categories.reduce((map, cat) => {
                map[cat.id] = cat;
                return map;
            }, {});
        },
        SET_COURSES(state, courses) {
            state.courses = courses;
        },
        SET_CURRENT_USER(state, user) {
            state.currentUser = user;
        },
        SET_GROUPS(state, groups) {
            state.groups = groups;
        },
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        SET_ERROR(state, error) {
            state.error = error;
        },
    },
    actions: {
        setCategories({ commit }, categories) {
            commit('SET_CATEGORIES', categories);
        },
        setCourses({ commit }, courses) {
            commit('SET_COURSES', courses);
        },
        setCurrentUser({ commit }, user) {
            commit('SET_CURRENT_USER', user);
        },
        setGroups({ commit }, groups) {
            commit('SET_GROUPS', groups);
        },
        setLoading({ commit }, loading) {
            commit('SET_LOADING', loading);
        },
        setError({ commit }, error) {
            commit('SET_ERROR', error);
        },
    },
});
