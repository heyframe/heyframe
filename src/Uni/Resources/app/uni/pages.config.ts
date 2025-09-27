import { defineUniPages } from '@uni-helper/vite-plugin-uni-pages'

export default defineUniPages({
  globalStyle: {
    backgroundColor: '@bgColor',
    backgroundColorBottom: '@bgColorBottom',
    backgroundColorTop: '@bgColorTop',
    backgroundTextStyle: '@bgTxtStyle',
    navigationBarBackgroundColor: '#fff',
    navigationBarTextStyle: '@navTxtStyle',
    navigationBarTitleText: 'HeyFrame',
    navigationStyle: 'custom',
  },
  // tabBar: {
  //   backgroundColor: "@tabBgColor",
  //   borderStyle: "@tabBorderStyle",
  //   color: "@tabFontColor",
  //   selectedColor: "@tabSelectedColor",
  // },
})
