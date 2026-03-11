<template>
  <v-card>
    <v-card-title class="first-row"
    >Редактирование пользователя {{ userData.fio }}
    </v-card-title
    >
    <v-row class="first-row">
      <v-col>
        <v-text-field
            variant="solo"
            label="ФИО"
            type="text"
            v-model="localUser.fio"
        ></v-text-field>
      </v-col>
      <v-col>
        <v-combobox
            variant="solo"
            v-model="localUser.role"
            dense
            label="Роль"
            :items="['Администратор', 'Инструктор', 'Обучаемый']"
            :return-object="false"
            :auto-select-first="true"
            @update:model-value="value => localUser.role = value"
        ></v-combobox>
      </v-col>
      <v-col>
        <v-text-field
            variant="solo"
            id="phonenumber"
            name="phonenumber"
            label="Телефон"
            v-model="localUser.phonenumber"
            type="text"
        ></v-text-field>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-autocomplete
            variant="solo"
            v-model="localUser.city"
            :items="options"
            label="Город"
            :searchable="true"
            :filter="customFilter"
            no-data-text="нет такого города"
            :return-object="false"
            :auto-select-first="true"
            @update:model-value="value => localUser.city = value"
        ></v-autocomplete>
      </v-col>
      <v-col>
        <v-text-field
            variant="solo"
            id="country"
            name="country"
            label="Страна"
            v-model="localUser.country"
            type="text"
        ></v-text-field>
      </v-col>
      <v-col>
        <v-text-field
            variant="solo"
            id="organization"
            name="organization"
            label="Организация"
            v-model="localUser.organization"
            type="text"
        ></v-text-field>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-text-field
            variant="solo"
            id="position"
            name="position"
            label="Должность"
            v-model="localUser.position"
            type="text"
        ></v-text-field>
      </v-col>
      <v-col>
        <v-select
            attach="false"
            
            variant="solo"
            v-model="localUser.rank"
            dense
            label="Воинское звание"
            :items="['капитан', 'майор', 'старший лейтенант', 'лейтенант']"            
        ></v-select>
      </v-col>
      <v-col>
        <v-combobox
            variant="solo"
            v-model="localUser.spfere"
            dense
            label="Сфера деятельности"
            :items="sfereOptions"
            :return-object="false"
            :auto-select-first="true"
            @update:model-value="value => localUser.spfere = value"
        ></v-combobox>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-text-field
            variant="solo"
            id="specialization"
            name="specialization"
            label="Специализация"
            v-model="localUser.specialization"
            type="text"
        ></v-text-field>
      </v-col>
      <v-col>
        <v-combobox
            variant="solo"
            label="Группа"
            type="text"
            :items="allGroups"
            v-model="localUser.group_id"
            item-value="id"
            item-title="groupname"
            :return-object="false"
            :auto-select-first="true"
            @update:model-value="value => localUser.group_id = value"
        ></v-combobox>
      </v-col>
      <v-col></v-col>
    </v-row>
    <div class="d-flex justify-space-between">
      <!-- <div class="d-flex justify-space-around"> -->
      <!-- <v-btn
        tile
        color="green"
        class="my-3"
        :to="{
          name: 'user.chroll',
          params: {
            idEdit: user.id,
          },
        }"
      >
        Роль</v-btn
      > -->
      <div>
        <v-btn
            style="margin-left: 10px"
            tile
            color="green"
            class="my-3"
            @click="dialog=true"
        >
          Разрешение
        </v-btn
        >
      </div>
      <ButtonGroup @submitForm="submitForm" @cancelBtn="cancelBtnHead"></ButtonGroup>
    </div>
    <!-- <v-select
      label="Разрешения"
      type="text"
      :items="permissions"
      v-model="user.permissions"
      multiple
      item-value="id"
      item-title="name"
      empty-option
    ></v-select> -->

    <v-container class="notification is-danger" v-if="errors.length">
      <!--class="has-text-centered"> -->
      <p v-for="error in errors" v-bind:key="error">
        {{ error }}
      </p>
    </v-container>
    <v-dialog v-model="dialog">
      <UserChperm :idEdit="idEdit" @submitForm="dialogFalse" @cancelBtn="cancelBtn"></UserChperm>
    </v-dialog>
    <!-- <v-dialog v-model="dialogReg">
      <UserLearning :idEdit="user.id" @submitForm="this.dialogReg = false" @cancelBtn="cancelBtnRegistration"></UserLearning>
    </v-dialog> -->
  </v-card>
</template>

<script>

import $api from "../../api/httpClient";
import {mapState, mapGetters} from 'vuex'
import jsonData from "../User/russia.min.json";
import sferejsonData from "../User/sfere.json";
//import UserLearning from "./UserLearning.vue";
//import jsonData from "../User/city.json";
import UserChperm from "./UserChperm.vue";
import ButtonGroup from "../../components/ButtonGroup.vue";

export default {
  components: {UserChperm, ButtonGroup},
  props:
      {
        idEdit: {
          type: Number,
          required: true
        },
      },
  data() {
    return {
      dialog: false,
      dialogReg: false,
      errors: [],
      options: jsonData,
      sfereOptions: sferejsonData,
      localUser: {
        fio: '',
        role: '',
        phonenumber: '',
        city: '',
        country: '',
        organization: '',
        position: '',
        rank: '',
        spfere: '',
        specialization: '',
        group_id: null
      }
    };
  },
  computed: {
    ...mapState('User', ['allGroups', 'user']),
    userData: {
      get() {
        return this.user || this.localUser;
      },
      set(value) {
        this.localUser = { ...value };
      }
    }
  },
  watch: {
    user: {
      immediate: true,
      handler(newValue) {
        if (newValue) {
          this.localUser = { ...newValue };
        }
      }
    }
  },
  created() {
    this.$store.dispatch('User/fetchUser', this.idEdit)
  },
  methods: {
    customFilter(item, queryText) {
      const text = item.toLowerCase();
      const query = queryText.toLowerCase();
      return text.indexOf(query) > -1;
    },
    cancelBtnRegistration(){      
      // this.$store
      //     .dispatch('Course/categories')
      //     .catch(error => console.error(error))
      this.$store.dispatch('User/fetchUser', this.idEdit)
      this.dialogReg = false
    },
    cancelBtnHead(){
      this.$router.back()
    },
    dialogFalse(){
      this.dialog = false
    },
    cancelBtn(){      
      this.$store.dispatch('User/fetchUser', this.idEdit)
      this.dialog = false
    },

    submitForm() {
       if (!this.errors.length) {
         const formData = {
           fio: this.localUser.fio,
           role: this.localUser.role,
           phonenumber: this.localUser.phonenumber,
           city: this.localUser.city,
           country: this.localUser.country,
           organization: this.localUser.organization,
           position: this.localUser.position,
           rank: this.localUser.rank,
           spfere: this.localUser.spfere,
           specialization: this.localUser.specialization,
           group_id: this.localUser.group_id,
         };

        $api
          .patch(`/api/user/${this.idEdit}`, formData)
          .then(() => {
            this.$router.push({ name: 'user.list' });
          })
          .catch((error) => {
            if (error.response) {
              this.errors = error.response.data.errors;
            }
          });
       }
    },

    // },
  },
};
</script>

<style>
.v-card {
  padding: 10px;
}

.first-row {
  margin-top: 20px;
}

.v-field {
  background-color: #f4f4f4;
}
</style>

