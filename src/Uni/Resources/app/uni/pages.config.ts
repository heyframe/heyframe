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
  tabBar: {
    backgroundColor: "@tabBgColor",
    borderStyle: "@tabBorderStyle",
    color: "@tabFontColor",
    selectedColor: "@tabSelectedColor",
    list: [
      {
        "pagePath": "pages/index/index",
        "text": "首页",
      },
      {
        "pagePath": "pages/product/product",
        "text": "产品",
      },
      {
        "pagePath": "pages/posts/posts",
        "text": "动态",
      },
      {
        "pagePath": "pages/account/account",
        "text": "我的",
      }
    ]
  },
})
