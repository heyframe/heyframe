import {useHeyUni} from "@/heyuni-instance";
import App from './App.vue'
import '@/app/main';
import 'uno.css'
import {createSSRApp} from "vue";
import VueAdapter from "@/app/adapter/view/vue.adapter";

/** Application Bootstrapper */
const {Application} = useHeyUni();

let apiConfig = {
  baseURL: import.meta.env.VITE_API_BASE_URL,
  accessToken: import.meta.env.VITE_API_ACCESS_TOKEN || ''
}

// #ifdef H5
if (import.meta.env.DEV) {
  apiConfig.baseURL = '/front-api'
}
// #endif

apiConfig =
  (typeof window !== 'undefined' && (window as any).apiConfig) || apiConfig


export function createApp() {
  const app = createSSRApp(App)
  Application.setViewAdapter(new VueAdapter(Application, app));
  Application.start({
    apiContext: {
      baseURL: apiConfig.baseURL,
      accessToken: apiConfig.accessToken,
    }
  }).then(()=>{});
  return {
    app: app
  }
}
