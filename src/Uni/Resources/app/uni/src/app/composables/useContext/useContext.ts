import type { Ref } from "vue";
import { computed, unref } from "vue";
import HeyUni from "@/heyuni-instance";
export function useContext<T>(
  key: string,
  params?: {
    context?: Ref<T> | T;
    replace?: T;
  }
): Ref<T> {
  let contextStore = HeyUni.Store.get('context');
  if (params?.context) {
    contextStore.set(key, params.context);
  }

  if (params?.replace) {
    contextStore.set(key, params.replace);
  }

  return computed<T>({
    get() {
      return contextStore.get<T>(key);
    },
    set(v: T) {
      contextStore.set(key, v);
    },
  });
}
