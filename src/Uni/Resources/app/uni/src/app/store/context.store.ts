/**
 * @sw-package framework
 */

import { ref, unref, Ref } from "vue";
import HeyUni from "@/heyuni-instance";
import apiContextStore from "@/app/store/api.context.store";

export type ContextEntry<T = any> = {
  key: string;
  value: T | null;
};

const contextStore = HeyUni.Store.register({
  id: 'context',
  state: () => ({
    contexts: [] as ContextEntry[],
  }),

  actions: {
    get<T = any>(key: string): T | null {
      const entry = this.contexts.find(ctx => ctx.key === key);
      return entry ? entry.value : null;
    },


    set<T = any>(key: string, value: Ref<T> | T): void {
      const rawValue = unref(value);
      const index = this.contexts.findIndex(ctx => ctx.key === key);

      if (index >= 0) {
        this.contexts[index].value = rawValue;
      } else {
        this.contexts.push({ key, value: rawValue });
      }
    },

    remove(key: string): void {
      this.contexts = this.contexts.filter(ctx => ctx.key !== key);
    },
  },
});

/**
 * @private
 */
export type ContextStore =  ReturnType<typeof contextStore>;

/**
 * @private
 */
export default contextStore;
