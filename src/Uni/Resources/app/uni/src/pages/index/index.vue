<template>
  <view class="content">
    <image class="logo" src="/static/logo.png"/>
    <view class="text-area">
      <text class="title">{{ userInfo?.nickname }}</text>
    </view>
  </view>
</template>

<script setup lang="ts">
import {ref,onMounted} from 'vue'
import HeyUni from "@/heyuni-instance";
import AccountService from "@/core/service/api/account.service";
import {useUser} from "@/app/composables/useUser/useUser";
import {useListing} from "@/app/composables/useListing/useListing";

const title = ref('Hello')


// let loginService = HeyUni.Service('accountService') as AccountService;
// loginService.loginByEmail('test@test.com','heyframe');


const userComposable = useUser();
const userInfo = ref(null);
onMounted(async () => {
  await userComposable.login({ username: 'test@test.com', password: 'heyframe' });
  userInfo.value = userComposable.user.value

  const { search, getElements } = useListing({
    listingType: "categoryListing",
    categoryId: "77b959cf66de4c1590c7f9b7da3982f3", // entrypoint to browse
    defaultSearchCriteria: { // set the default criteria
      limit: 3,
      p: 1,
    },
  });

  search({ // invoke search() method
    includes: { // omit this parameter if you want to use the whole product entity
      product: ["id", "name", "cover", "calculatedPrice", "translated"],
      product_media: ["media"],
      media: ["url", "thumbnails"],
    },
  });

});

</script>

<style>
.content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.logo {
  height: 200rpx;
  width: 200rpx;
  margin-top: 200rpx;
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 50rpx;
}

.text-area {
  display: flex;
  justify-content: center;
}

.title {
  font-size: 36rpx;
  color: #8f8f94;
}
</style>
