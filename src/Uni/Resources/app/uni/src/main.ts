import HeyUni from "@/heyuni-instance";
import '@/app/main';
import 'uno.css'

let apiConfig = {
  baseURL: import.meta.env.VITE_API_BASE_URL,
  accessToken: import.meta.env.VITE_API_ACCESS_TOKEN || ''
}

// #ifdef H5
if(import.meta.env.DEV){
  apiConfig.baseURL = '/front-api'
}
// #endif

apiConfig =
  (typeof window !== 'undefined' && (window as any).apiConfig) || apiConfig


void (async () => {
  await HeyUni.Application.start({
    apiContext: {
      baseURL: apiConfig.baseURL,
      accessToken: apiConfig.accessToken,
    }
  });
})();
