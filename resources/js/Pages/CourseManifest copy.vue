<template>
  {{ idEdit }}--{{ idCategory }}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" />
  <!-- {{ activeId }} -->
  <!-- {{ aukstructures }}--- -->
  <!-- {{ filterByCategoryAukstructures }}   -->
  <!-- {{ categoryCode }} -- -->
  <!-- {{ isFavorite }} -->
  <!-- {{ favorites }} -->
  <!-- {{ matchingFiles.length}} -->
  <!-- {{ matchingFiles.contents}} -->
  <!-- <v-card class="bg-grey"> -->
  <!-- {{ getFirstAukId }} -->
  <!-- {{  computedLink  }} -->
  {{ link }}
  {{ searchTerm }}--

  <v-card color="#f5f5f5">
    <v-row dense no-gutters>
      <v-col cols="3">
        <!-- <v-sheet rounded elevation="4" class="flex-child text-subtitle-1 pa-2 mt-1"> -->
        <v-sheet class="my-sheet pa-2 mt-1" color="#f5f5f5" :style="{ overflow: 'auto', 'overflow-y': 'auto' }">
          <!-- кнопки для поиска в тексте,добавления в избранное -->
          <v-sheet class="mx-auto mt-0 mb-3" elevation=4 rounded=lg>
            <div class="text-center" :style="{ fontSize: '20px' }">{{ titleauk.toUpperCase() }}</div>
          </v-sheet>
          <v-row no-gutters align="center ">
            <v-col cols="1" class="row-with-line"></v-col>
            <v-col cols="4" class="d-flex align-center">
              <v-icon size="x-large" class=" icon-list" :class="{ active: showItems }"  @click="toggleList">{{ showItems ?
                'mdi-view-list' : 'mdi-view-list-outline'
              }}</v-icon>
              <v-icon size="x-large" class="icon-favorite" :class="{ active: isFavorite }" @click="toggleFavorite">{{
                isFavorite ? 'mdi-heart' : 'mdi-heart-outline' }}</v-icon>
              <v-icon size="x-large" class="icon-search" :class="{ active: showSearch }" @click="toggleSearch">{{
                showSearch ? 'mdi-magnify-minus-outline' : 'mdi-magnify' }}</v-icon>
            </v-col>
            <v-col cols="6" class="row-with-line "></v-col>
            <v-col cols="1" class="d-flex align-center">
              <v-icon size="x-large" @click="addToFavorites(activeId)" icon="mdi-playlist-star" class="addToFav"></v-icon>
            </v-col>
          </v-row>

          <v-row v-if="isFavorite" class="ml-1 mr-1">
            <ul>
              <li v-for="item in favorites" :key="item.id">
                {{ item.title }}
                <font-awesome-icon icon="times" @click="removeFavorite(item.course_id)" />
              </li>
            </ul>
          </v-row>

          <v-row v-if="showSearch">
            <v-text-field class="ml-5 mr-5" :loading="loading" density="compact" v-model="searchTerm" variant="outlined"
              rounded append-inner-icon="mdi-magnify" label="Поиск" @click:append-inner="search" @keyup.enter="search"
              hint="Введи искомый текст для поиска" clearable single-line>
            </v-text-field>

            <!-- ------------------------------------------------ -->
            <!-- результаты поиска -->


            <div>
              <ul v-if="matchingFiles.length > 0" class=" ml-2 mr-2 search-files__total-results">Всего найдено: {{
                matchingFiles.length }}
<v-btn-group >                  
                  <v-btn @click="scrollToPrev"><span>&#9650;</span></v-btn>                
                  <v-btn @click="scrollToNext" ><span>&#9660;</span></v-btn>
                </v-btn-group>
                  <v-divider></v-divider>
                  <li v-for="result in matchingFiles" :key="result.file" style="white-space: nowrap;">
                  <v-btn @click="loadContent(result.contents)"  class="text-truncate"  :style="{ 'max-width': '100%', 'overflow': 'hidden', 'text-overflow': 'ellipsis' }">{{ result.title }}</v-btn>         
                </li>
              </ul>
              <p v-else class="ml-5 mr-5 mt-1 search-files__no-results">Нет результатов</p>
            </div>

            <!-- ------------------------------------------------ -->
            <!-- результаты поиска -->



          </v-row>
          <!-- <div v-if="showItems" v-for="(item, index) in filterByCategoryAukstructures" :key="item.parent_id"> -->
          <div v-if="showItems" v-for="(item, index) in aukstructures" :key="item.parent_id">
            <div class="mt-1 mx-3" :style="[
              item.type !== 3
                ? {
                  cursor: 'default',
                  opacity: '.7',
                  color: 'green',
                }
                : {
                  cursor: 'pointer',
                  //border: '2px solid firebrick',
                },
              {
                fontSize: `${-5 * item.type + 30}px`,
                //transform: `translate(${item.type * 20}px)`,
                paddingLeft: `${(item.type - 1) * 10}px`,
                display: 'inline-block',
                wordWrap: 'break-word',
              },
            ]">
              <div @mouseover="item.type === 3 ? showthumb(item.id) : ''" @mouseleave="hidethumb(item.id)" :id="item.id"
                @click="item.type === 3 ? getlink(item.id) : ''" v-if="index !== 0">
                {{ item.title }}
                <!-- {{ item.id }}--{{ item.title }} -->
              </div>
            </div>
          </div>
        </v-sheet>
      </v-col>

      <v-col cols="9">
        <!-- <v-sheet rounded class="pa-2 mt-1" elevation="4"> -->
        <v-sheet rounded elevation="5" class="my-sheet pa-2 mt-2 mr-2"
          :style="{ 'border-radius': '8px', overflow: 'auto', 'overflow-y': 'auto' }">
          <div id="iframe-container" :style="{ 'border-radius': '8px', overflow: 'auto', 'overflow-y': 'auto' }">

            <!-- <iframe class="hello px-5" :src="link"
              onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+'px';" :style="contentStyleObj"
              width="100%" name="iframe_a" >
            </iframe> -->

            <iframe class="hello px-5" :src="link" ref="myIframe" name="iframe_a" 
            @load="updateDocHeight"       
               :style="contentStyleObj" width="100%" >
            </iframe>

            <!-- <iframe class="hello px-5" :src="link"  name="iframe_a" @load="scrollToAnchor"
  
  onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+'px'; setTimeout(() => { scrollToAnchor(window.location.hash); }, 100);"
  :style="contentStyleObj" width="100%" 
  >
</iframe> -->
<!-- @load="updateDocHeight" -->

<!-- onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+'px'; " -->

          </div>
        </v-sheet>
      </v-col>
    </v-row>
  </v-card>
  <popup :alert="alert" :alertType="alertType" :snackbarText="snackbarText" :overlay="alert" :alertFalse="alertFalse">
  </popup>
</template>

<!-- <script> -->
<script export default>

const apiUrl = import.meta.env.VITE_APP_URL;
import $api from "../api/httpClient";
import popup from "./Popup.vue";
import { mapState, mapGetters } from "vuex";
import { library } from '@fortawesome/fontawesome-svg-core';
import { faTimes } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
library.add(faTimes);
export default {
  components: {
    popup, FontAwesomeIcon,

  },

  props: {
    idEdit: {
      type: Number,
      required: true,
    },
    idCategory: {
      type: Number,
      required: true,
    },
    treeData: Object,
  },


  data() {

    return {

      titleauk: "",
      aukstructures: [],
      filterByCategoryAukstructures: [],
      categories: {},
      link: "",
      firstId: '',
      // contentStyleObj: {
      //   height: "",
      // },
      activeId: this.firstId,
      curAuk: this.item,


      showItems: true,
      showSearch: false,
      isFavorite: false, // добавить в избранное
      //---------------- блок имитации загрузки--------
      loaded: false,
      loading: false,
      //----------- конец блока имитации загрузки--------
      //------------блок поиска---------------
      searchTerm: '',
      matchingFiles: [],
      aircraftTitle: '', //передавать в контроллер 
      //------------конец блока поиска---------------
      path: '',
      aircraft: '',

      //
      error: '',
      //---- for popup
      isLoading: false,
      alert: false,
      alertType: "",
      overlay: false,
      snackbarText: "",
      //
      // избранное
      favorites: [],
      // загрузка iframe
      highlighted: [],
      currentHighlight: 0,
      
    };
  },
  //----------------достучаться до iframe--------------
  // beforeUnmount() {
  //   const iframe = this.$refs.myIframe;
  //   iframe.removeEventListener("load", this.handleIframeLoad);
  // },

  async mounted() {
    //----------------достучаться до iframe--------------
//    const iframe = this.$refs.myIframe;
    //iframe.addEventListener("load", this.handleIframeLoad);



    // iframe.contentWindow.addEventListener('message', (event) => {
    //   if (event.data === 'updateContent') {
    //     let height = iframe.contentWindow.document.body.scrollHeight + 20;
    //     iframe.style.height = `${height}px`;
    //   }
    // });

    //----------------достучаться до iframe--------------

    //console.log('idEdit:', this.idEdit);
    //console.log('idCategory:', this.idCategory);
    this.getFavorites(); // загружаем избранное
    //console.log(apiUrl, "apiUrl")
    //this.$store.dispatch("Course/fetchCourse",  { courseId: this.idEdit, categoryId: this.category_id });    
    if (!this.aircrafts) {
      await this.$store.dispatch("Course/fetchAircrafts");
    }
    this.$store.dispatch("Course/fetchCourse", this.idEdit);
    this.$store.dispatch("Course/fetchCategory", this.idCategory);
    this.$store.dispatch("Course/fetchCategories");
    this.$store.dispatch("Course/fetchAircrafts");
    this.$store.dispatch("Course/fetchAircraft", this.aircraft);
    // загрузка левого меню
    //this.$store.dispatch("Course/fetchCourse", { course_id: this.idEdit, category_id: this.idCategory })
    $api
      .get(apiUrl + "/api/course?course_id=" + this.idEdit + "&&category_id=" + this.idCategory)
      .then((response) => {
        //console.log(response.data[0].aircraft_id, "air");
        this.titleauk = response.data[0].title;
        //console.log(response[0].title, "response");
        this.aukstructures = response.data[0].aukstructures; // получаем с backEnd все aukstruct для построения меню левого
        //console.log(this.aukstructures)
        // this.filterByCategoryAukstructures = this.aukstructures.filter((aukstructure) => {
        //   return aukstructure.categories.includes('K');
        // });      

        // фильтруем по категориям

        this.filterByCategoryAukstructures = this.aukstructures.filter((aukstructure) => {
          return aukstructure.categories ? aukstructure.categories.includes(this.categoryCode.toString().trim()) : true;
        }).sort((a, b) => a.id - b.id);

        //this.getfirstauk(this.idEdit);
        //удалить возможно
        this.path = response.data[0].path; // папка с АУК
        //console.log(response.data.aircraft_id, "response.data.aircraft")        
        this.aircraft = response.data[0].aircraft_id;

        // const aircraftTitle = this.aircrafts.find(a => a.id === this.aircraft);
        //console.log(aircraftTitle.path, "aircraftTitle")
      })
      .catch((err) => {
        console.error(err);
      });
  },

  watch: {
    //   searchTerm() {
    //   this.highlightIframeContent();
    // },
    link(newLink, oldLink) {
    console.log('Link has changed:', oldLink, '->', newLink);
    // Вызов метода loadContent для загрузки нового контента
    
  },

    activeId(newVal, oldVal) {
      //console.log("active",this.activeId)
    },
    getFirstAukId: function (newVal, oldVal) {
      // вызываем метод getlink с новым значением
      if (newVal) {
        this.getlink(newVal);
      }
    }
  },
  computed: {
    ...mapState("Course", ["course", "category", "totalCourses", "aircrafts", "aircraft"]),
    ...mapGetters("Course", ["categories", "courses"]),
    // indent() {
    //   return {fontSize: `${-4 * this.item.type + 30}px`,transform: `translate(${this.item.type * 20}px)`, };
    // }, 

    // computedLink() {
    //   return (path) => {        
    //     return 'api/' + path;
    //   }

    // computedLink() {
    //   // Возвращаем функцию, принимающую два параметра: file и position
    //   return (file, position) => {
    //     const encodedPosition = encodeURIComponent(position);
    //     //console.log(position,"position");
    //     // Формируем ссылку с якорем
    //     return `api/${file}#${encodedPosition}`;
    //     // return `api/${file}#${position}`;
    //   }
    // },

    idEditComputed() {
      return this.idEdit;
    },
    idCategoryComputed() {
      return this.idCategory;
    },
    // возвращаем из vuex category categoryCode по id
    categoryCode() {
      return this.category ? this.category.code : null;
    },
    getFirstAukId() {
      // Используем метод find() для поиска первого элемента, у которого type равен 3
      const firstAuk = this.aukstructures.find((item) => item.type === 3);
      // Если элемент найден, то возвращаем его id, иначе возвращаем null      
      if (firstAuk) this.getlink(firstAuk.id);
      return firstAuk ? firstAuk.id : null;
    },
  },
  methods: {
    updateDocHeight() {      
      console.log("onIframeLoad")
      const myIframe = this.$refs.myIframe;      
      myIframe.style.height = `${myIframe.contentWindow.document.body.scrollHeight + 20}px`;      
    },

     restoreContent() {
//       console.log("restore content")
       this.searchTerm='';
      
//   let iframe = this.$refs.myIframe;
//   iframe.srcdoc = this.prevHtml;
//   //this.onIframeLoad();
 },
    ///---------------------рабочий с заменой body целиком--------------------
  
    clearContent() {
      console.log("clear")
      this.searchTerm='';
      // Use a ref to reference the content element
      const iframe = this.$refs.myIframe;
      try {
    let iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    console.log(iframeDoc.getElementsByTagName('body'), 'body')
    iframeDoc.getElementsByTagName('body')[0].innerHTML = "";    
    iframe.setAttribute('srcdoc', '');
    // iframe через доступ к его contentDocument и вызова метода open(), write() и close()
    iframeDoc.open();
    iframeDoc.write();
    iframeDoc.close();
    
    //this.restoreContent() 
    
  } catch (error) {
    console.error('Failed to clear iframe content:', error);
  }
    },

loadContent(newHtml) {
  let iframe = this.$refs.myIframe;
  //prevHtml
  let prevHtml = iframe.contentDocument?.body.innerHTML || '';
  //console.log(prevHtml, "prevHtml")
  //if(this.searchTerm)
  //{
  iframe.onload = () => { 
    
    let iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    //console.log(newHtml, "newHtml")
    console.log(iframeDoc.querySelector('body'), "newHtml")
    //iframeDoc.getElementsByTagName('body')[0].innerHTML = newHtml;
    iframeDoc.querySelector('body').innerHTML = newHtml;
    let highlight = iframeDoc.querySelectorAll('.highlighted')
    this.highlighted = Array.from(highlight);
    highlight.forEach((el) => {
      el.style.backgroundColor = 'yellow';
    });  
    if (this.highlighted.length > 0) {
      this.currentHighlight = 0;
      this.scrollToHighlight(this.highlighted[this.currentHighlight]);        
    }
  };  
 
//}
this.prevHtml = prevHtml; // предыдущее содержимое без подсветки
  iframe.src = this.link;
  //console.log(this.link,"this.link")
  
},                                   
///---------------------рабочий с заменой body целиком--------------------



// scrollToHighlight(highlight) {
//   let iframe = this.$refs.myIframe;
//   let topOffset = highlight.offsetTop;
//   iframe.contentWindow.scrollTo({
//     top: topOffset,
//     behavior: 'smooth'
//   });
// },
scrollToHighlight(highlight) {
  let iframe = this.$refs.myIframe;
  let topOffset = highlight.getBoundingClientRect().top + iframe.contentWindow.pageYOffset - iframe.contentDocument.documentElement.clientTop;
  iframe.contentWindow.scrollTo({
    top: topOffset,
    behavior: 'smooth'
  });
},

scrollToNext() {
      if (this.highlighted.length > 0) {
        console.log("к следующему")
        this.currentHighlight = (this.currentHighlight + 1) % this.highlighted.length;
        this.scrollToHighlight(this.highlighted[this.currentHighlight]);
      } else {
        this.currentHighlight = 0;
      }
    },
    scrollToPrev() {
      if (this.highlighted.length > 0) {
        console.log("к предыдующему")
        this.currentHighlight = (this.currentHighlight - 1 + this.highlighted.length) % this.highlighted.length;
        this.scrollToHighlight(this.highlighted[this.currentHighlight]);
      } else {
        this.currentHighlight = 0;
      }    
  },



// loadContent(newHtml) {
//   let iframe = this.$refs.myIframe;

//   iframe.onload = function () {
//     let iframeDoc = iframe.contentWindow.document;

//     // Устанавливаем содержимое body внутри iframe
//     iframeDoc.getElementsByTagName('body')[0].innerHTML = newHtml;

//     // Создаем новый экземпляр MutationObserver
//     let observer = new MutationObserver(function (mutations) {
//       mutations.forEach(function (mutation) {
//         if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
//           let highlighted = iframeDoc.querySelectorAll('.highlighted');
//           highlighted.forEach((el) => {
//             el.style.backgroundColor = 'yellow';
//           });
//           observer.disconnect();
//         }
//       });
//     });

//     // Настраиваем MutationObserver для отслеживания добавления элементов в DOM
//     observer.observe(iframeDoc.body, { childList: true, subtree: true });
//   };

//   iframe.src = this.link;
// },




// loadContent(newHtml) {
//   let iframe = this.$refs.myIframe;

//   // Обработчик события загрузки iframe
//   iframe.onload = function () {
//     // Получаем документ iframe
//     let iframeDoc = iframe.contentWindow.document;

//     // Загружаем содержимое файла из this.link
//     fetch(this.link)
//       .then(response => response.text())
//       .then(originalHtml => {
//         // Заменяем содержимое тега body на оригинальный HTML-код
//         iframeDoc.getElementsByTagName('body')[0].innerHTML = originalHtml;

//         // Заменяем подсвеченный текст на новый HTML-код
//         iframeDoc.getElementsByTagName('body')[0].innerHTML = newHtml;
//       })
//       .catch(error => {
//         console.error(error);
//       });
//   }.bind(this);

//   // Загружаем пустую страницу в iframe, чтобы сбросить его содержимое
//   iframe.src = 'about:blank';
// },
    ///---------------------рабочий с заменой body целиком--------------------


    showthumb(item_id) {
      // console.log(item_id)
      document.getElementById(item_id).style.border = "2px doted grey ";
      document.getElementById(item_id).style.borderRadius = "4px";
      if (item_id !== this.activeId)
        document.getElementById(item_id).style.background = "#D3D3D3";
      // document.getElementById(item_id).style.fontWeight="bold";            
      document.getElementById(item_id).style.transform = "scale(1.03)";
    },
    hidethumb(item_id) {
      //console.log(item_id, 'вышел')
      document.getElementById(item_id).style.border = "none";
      if (item_id !== this.activeId)
        document.getElementById(item_id).style.background = "none";
      // document.getElementById(item_id).style.fontWeight="normal";            
      document.getElementById(item_id).style.transform = "scale(1.0)";
    },
    getlink(item_id) {
      //console.log("getlink")

      //console.log(old, "old");
      //document.getElementById(this.activeId).style.background = "blue";
      //console.log(this.activeId, "this.activeId");
      this.activeId = item_id;
      //document.getElementById(item_id).style.background = "red";
      //document.getElementById(item_id).className ='xxx'      
      //document.getElementById(item_id).classList.add('xxx')
      // const selectedTextArea = document.getElementById(item_id);
      // console.log(selectedTextArea,"selectedTextArea")
      // selectedTextArea.style.background = "red";
      $api
        .get(apiUrl + "/api/getlink/" + item_id)
        .then((response) => {
          //console.log(item_id, "itemid");
          this.link = response.data;
          //console.log(response.data, "response.data")
        });
        
    },

    getfirstauk: function (course_id) {
      $api
        .get(apiUrl + "/api/getfirstauk/" + course_id)
        .then((response) => {          
          this.firstId = response.data;
          //console.log(this.firstId,"this.firstId")
          this.getlink(this.firstId);
        });
    },
    // добавить в избранное
    toggleFavorite() {
      this.isFavorite = !this.isFavorite;
      this.showItems = false;
      this.showSearch = false;
      if (this.isFavorite == false) this.showItems = true;
    },
    toggleList() {
      this.clearContent();
      this.showItems = !this.showItems;
      this.isFavorite = false;
      this.showSearch = false;
    },
    toggleSearch() {
      this.showSearch = !this.showSearch;
      this.isFavorite = false;
      this.showItems = false;
      if (this.showSearch == false) this.showItems = true;
    },
    onClickSearch() {
      this.loading = true

      setTimeout(() => {
        this.loading = false
        this.loaded = true
      }, 6000)


    },

    addToFavorites(course_id) {
      const activeTitle = this.aukstructures.find(item => item.id === course_id)?.title;
      // console.log(activeTitle, "activeTitle")
      $api
        .post(apiUrl + '/api/favorites/add', { course_id: course_id, title: activeTitle })
        .then(response => {
          // Обработка успешного добавления в избранное
          this.getFavorites(); // загружаем избранное
        })
        .catch(error => {
          // Обработка ошибки
        });
    },

    getFavorites() {
      $api.get(apiUrl + '/api/favorites/').then((response) => {
        this.favorites = response.data.favorites;
      });
    },
    removeFavorite(id) {
      $api
        .delete(apiUrl + `/api/favorites/${id}`).then(() => {
          this.getFavorites();
        });
    },



    alertFalse() {
      this.alert = false;
    },
    async search() {
      const formData = {
        //  query: this.searchTerm, path: this.path, aircraft: this.aircraft
        query: this.searchTerm, path: this.path, aircraft: this.aircraft

      }
      if (this.searchTerm.length <= 3) {

        this.snackbarText = "..не меньше трех символов";
        this.alertType = "error";
        this.alert = true;
        return;
      }
      //console.log(data,'data');
      //console.log(this.searchTerm,'search');      
      $api.post(apiUrl + `/api/search-files/`, formData)

        .then(response => {
          this.matchingFiles = response.data;
          //         console.log(response, "кол-во")
        })
        .catch(error => {
          console.log(error);
        }).finally(() => {
          // this.alert = true;
        });
    },

  },
};
</script>


<style>
.highlighted {
  background-color: yellow !important;
  display: inline-block;
}

/* .xxx:active {
  background: red;
  background-color: red;
} */


/* .xxx .v-chip__content {
  color: white;
  font-weight: bold;
} */

.v-card {
  border: 1px solid lightgrey;
}


.my-sheet {
  /* border: 1px solid black; */
  height: 800px;
  /* Установите высоту для v-sheet, чтобы прокрутка сработала */
}

/* .active {
  font-weight: bold;
  background-color: red;
} */
/*
div:hover {
  background-color: lightgray;
} */
/* реализуем подсветку */
/* .highlight {
  background-color: yellow;
  display: inline-block;
} */


/*  */


/*  */




/* .icon-list {
  border-top: 2px solid blue;
  border-left: 2px solid blue;
  border-right: 2px solid blue;
  border-bottom: 2px solid blue;
}

.icon-favorite {
  border-top: 2px solid red;
  border-left: 2px solid red;
  border-right: 2px solid red;
  border-bottom: 2px solid red;
}

.icon-search {
  border-top: 2px solid green;
  border-left: 2px solid green;
  border-right: 2px solid green;
  border-bottom: 2px solid green;
}
.active {
  border-bottom: none;
} */







/* .icon-list {
  position: relative;
  
} */

.icon-list::before {
  /* border-radius: 10%; */
  opacity: 0.5;
  transition: opacity 0.2s ease-in-out;
}

.icon-list:hover::before {
  opacity: 1;
}

/* .icon-favorite {
  position: relative;  
} */

.icon-favorite::before {
  /* border-radius: 10%; */
  opacity: 0.5;
  transition: opacity 0.2s ease-in-out;
}

.icon-favorite:hover::before {
  opacity: 1;
}

/* .icon-search {
  position: relative;  
} */

.icon-search::before {
  opacity: 0.5;
  transition: opacity 0.2s ease-in-out;
}

.icon-search:hover::before {
  opacity: 1;
}

.row-with-line {
  border-bottom: 2px solid green;
  margin-bottom: -31px;
}

.row-with-line .active {
  border-bottom: none !important;
}

.icon-list,
.icon-favorite,
.icon-search {
  /* margin-bottom: -6px; */
  border-bottom: 2px solid green;
  width: 130px;
  /* position: relative; */

}

.addToFav {
  /* width: 70px; */
  border-bottom: 2px solid green;
  border-top: 1px solid green;
  border-left: 1px solid green;
  border-right: 1px solid green;
  border-top-left-radius: 50%;
  border-top-right-radius: 10%;
}

.active {
  border-bottom: none;
  border-top: 2px solid green;
  border-left: 2px solid green;
  border-right: 2px solid green;
  border-top-left-radius: 15%;
  border-top-right-radius: 15%;
}

.search-files__total-results {
  font-size: 14px;
  color: #666;
}

/* .highlight {
  background-color: yellow;
} */
</style>
