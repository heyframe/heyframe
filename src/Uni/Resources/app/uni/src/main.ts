import HeyUni from "@/heyuni-instance";
import '@/app/main';

import { apiConfig } from '@/config/env';

void (async () => {
  await HeyUni.Application.start({
    apiContext: {
      baseURL: apiConfig.baseURL,
      accessToken: apiConfig.accessToken,
    }
  });
})();
