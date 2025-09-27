<script setup lang="ts">
import { onLaunch, onShow, onHide } from "@dcloudio/uni-app";
onLaunch((options) => {
  console.log('App Launch', options)
});
onShow(() => {

});
onHide(() => {
  console.log('App Hide')
});
</script>
<style lang="scss">

</style>
