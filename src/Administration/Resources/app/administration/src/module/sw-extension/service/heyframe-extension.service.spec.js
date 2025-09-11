import createHttpClient from 'src/core/factory/http.factory';
import createLoginService from 'src/core/service/login.service';
import StoreApiService from 'src/core/service/api/store.api.service';
import HeyFrameExtensionService from 'src/module/sw-extension/service/heyframe-extension.service';
import ExtensionStoreActionService from 'src/module/sw-extension/service/extension-store-action.service';
import AppModulesService from 'src/core/service/api/app-modules.service';
import 'src/module/sw-extension/service';
import initState from 'src/module/sw-extension/store';
import appModulesFixtures from '../../../app/service/_mocks/testApps.json';

jest.mock('src/module/sw-extension/service/extension-store-action.service');
jest.mock('src/core/service/api/app-modules.service');

const httpClient = createHttpClient(HeyFrame.Context.api);
HeyFrame.Application.getContainer('init').httpClient = httpClient;
HeyFrame.Service().register('loginService', () => {
    return createLoginService(httpClient, HeyFrame.Context.api);
});

HeyFrame.Service().register('storeService', () => {
    return new StoreApiService(httpClient, HeyFrame.Service('loginService'));
});

HeyFrame.Service().register('heyframeDiscountCampaignService', () => {
    return { isDiscountCampaignActive: jest.fn(() => true) };
});

/**
 * @sw-package checkout
 */
describe('src/module/sw-extension/service/heyframe-extension.service', () => {
    let heyframeExtensionService;

    beforeAll(() => {
        heyframeExtensionService = HeyFrame.Service('heyframeExtensionService');

        initState(HeyFrame);

        HeyFrame.Store.get('extensionEntryRoutes').routes = {
            ExamplePlugin: {
                route: 'test.foo',
            },
        };

        if (HeyFrame.Store.get('context')) {
            HeyFrame.Store.unregister('context');
        }

        HeyFrame.Store.register({
            id: 'context',
            state: () => ({
                app: {
                    config: {
                        settings: {
                            disableExtensionManagement: false,
                        },
                    },
                },
                api: {
                    assetPath: 'http://localhost:8000/bundles/administration/',
                    authToken: {
                        token: 'testToken',
                    },
                },
            }),
        });
    });

    describe('it delegates lifecycle methods', () => {
        const mockedExtensionStoreActionService = new ExtensionStoreActionService(
            httpClient,
            HeyFrame.Service('loginService'),
        );
        mockedExtensionStoreActionService.getMyExtensions.mockImplementation(() => {
            return ['new extensions'];
        });

        const mockedModuleService = new AppModulesService(httpClient, HeyFrame.Service('loginService'));
        mockedModuleService.fetchAppModules.mockImplementation(() => {
            return ['new app modules'];
        });

        const mockedHeyFrameExtensionService = new HeyFrameExtensionService(
            mockedModuleService,
            mockedExtensionStoreActionService,
            HeyFrame.Service('heyframeDiscountCampaignService'),
            HeyFrame.Service('storeService'),
        );

        function expectUpdateExtensionDataCalled() {
            expect(mockedExtensionStoreActionService.refresh).toHaveBeenCalledTimes(1);
            expect(mockedExtensionStoreActionService.getMyExtensions).toHaveBeenCalledTimes(1);

            expect(HeyFrame.Store.get('heyframeExtensions').myExtensions.data).toEqual(['new extensions']);
            expect(HeyFrame.Store.get('heyframeExtensions').myExtensions.loading).toBe(false);

            expectUpdateModulesCalled();
        }

        function expectUpdateModulesCalled() {
            expect(mockedModuleService.fetchAppModules).toHaveBeenCalledTimes(1);

            expect(HeyFrame.Store.get('heyframeApps').apps).toEqual([
                'new app modules',
            ]);
        }

        beforeEach(() => {
            HeyFrame.Store.get('heyframeExtensions').setMyExtensions([]);
            HeyFrame.Store.get('heyframeApps').apps = [];
        });

        it.each([
            [
                'installExtension',
                [
                    'someExtension',
                    'app',
                ],
            ],
            [
                'updateExtension',
                [
                    'someExtension',
                    'app',
                    true,
                ],
            ],
            [
                'uninstallExtension',
                [
                    'someExtension',
                    'app',
                    true,
                ],
            ],
            [
                'removeExtension',
                [
                    'someExtension',
                    'app',
                    true,
                ],
            ],
        ])('delegates %s correctly', async (lifecycleMethod, parameters) => {
            await mockedHeyFrameExtensionService[lifecycleMethod](...parameters);

            expect(mockedExtensionStoreActionService[lifecycleMethod]).toHaveBeenCalledTimes(1);
            expect(mockedExtensionStoreActionService[lifecycleMethod]).toHaveBeenCalledWith(...parameters);

            expectUpdateExtensionDataCalled();
        });

        it('delegates cancelLicense correctly', async () => {
            await mockedHeyFrameExtensionService.cancelLicense(5);

            expect(mockedExtensionStoreActionService.cancelLicense).toHaveBeenCalledTimes(1);
            expect(mockedExtensionStoreActionService.cancelLicense).toHaveBeenCalledWith(5);
        });

        it.each([
            ['activateExtension'],
            ['deactivateExtension'],
        ])('delegates %s correctly', async (lifecycleMethod) => {
            await mockedHeyFrameExtensionService[lifecycleMethod]('someExtension', 'app');

            expect(mockedExtensionStoreActionService[lifecycleMethod]).toHaveBeenCalledTimes(1);
            expect(mockedExtensionStoreActionService[lifecycleMethod]).toHaveBeenCalledWith('someExtension', 'app');

            expectUpdateModulesCalled();
        });
    });

    describe('checkLogin', () => {
        const checkLoginSpy = jest.spyOn(HeyFrame.Service('storeService'), 'checkLogin');

        beforeEach(() => {
            HeyFrame.Store.get('heyframeExtensions').userInfo = true;
        });

        it.each([
            [{ userInfo: { email: 'user@heyframe.com' } }],
            [{ userInfo: null }],
        ])('sets login status depending on checkLogin response', async (loginResponse) => {
            checkLoginSpy.mockImplementationOnce(() => loginResponse);

            await heyframeExtensionService.checkLogin();

            expect(HeyFrame.Store.get('heyframeExtensions').userInfo).toStrictEqual(loginResponse.userInfo);
        });

        it('sets login status to false if checkLogin request fails', async () => {
            checkLoginSpy.mockImplementationOnce(() => {
                throw new Error('something went wrong');
            });

            await heyframeExtensionService.checkLogin();

            expect(HeyFrame.Store.get('heyframeExtensions').loginStatus).toBe(false);
            expect(HeyFrame.Store.get('heyframeExtensions').userInfo).toBeNull();
        });
    });

    describe('isVariantDiscounted', () => {
        it('returns true if price is discounted and campaign is active', async () => {
            const variant = {
                netPrice: 100,
                discountCampaign: {
                    discountedPrice: 80,
                },
            };

            expect(heyframeExtensionService.isVariantDiscounted(variant)).toBe(true);
        });

        it('returns false if price is discounted but campaign is not active', async () => {
            const variant = {
                netPrice: 100,
                discountCampaign: {
                    discountedPrice: 80,
                },
            };

            HeyFrame.Service('heyframeDiscountCampaignService').isDiscountCampaignActive.mockImplementationOnce(() => false);

            expect(heyframeExtensionService.isVariantDiscounted(variant)).toBe(false);
        });

        it('returns false if variant is falsy', async () => {
            expect(heyframeExtensionService.isVariantDiscounted(null)).toBe(false);
        });

        it('returns false if variant has no discountCampaign', async () => {
            expect(heyframeExtensionService.isVariantDiscounted({})).toBe(false);
        });

        it('returns false if discounted price is net price', async () => {
            expect(
                heyframeExtensionService.isVariantDiscounted({
                    netPrice: 100,
                    discountCampaign: {
                        discountedPrice: 100,
                    },
                }),
            ).toBe(false);
        });
    });

    describe('orderVariantsByRecommendation', () => {
        it('orders variants by recommendation and discounting', async () => {
            const variants = [
                {
                    netPrice: 100,
                    discountCampaign: { netPrice: 100 },
                    type: 'rent',
                },
                {
                    netPrice: 100,
                    discountCampaign: { netPrice: 80 },
                    type: 'test',
                },
                {
                    netPrice: 100,
                    discountCampaign: { netPrice: 100 },
                    type: 'test',
                },
                {
                    netPrice: 100,
                    discountCampaign: { netPrice: 100 },
                    type: 'buy',
                },
                {
                    netPrice: 100,
                    discountCampaign: { netPrice: 10 },
                    type: 'rent',
                },
            ];

            heyframeExtensionService
                .orderVariantsByRecommendation(variants)
                .forEach((current, currentIndex, orderedVariants) => {
                    const isCurrentDiscounted = heyframeExtensionService.isVariantDiscounted(current);
                    const currentRecommendation = heyframeExtensionService.mapVariantToRecommendation(current);

                    orderedVariants.forEach((comparator, comparatorIndex) => {
                        const isComparatorDiscounted = heyframeExtensionService.isVariantDiscounted(comparator);
                        const comparatorRecommendation = heyframeExtensionService.mapVariantToRecommendation(comparator);

                        if (isCurrentDiscounted !== !isComparatorDiscounted) {
                            // discounted index is always smaller than undiscounted
                            if (isCurrentDiscounted && !isComparatorDiscounted) {
                                // eslint-disable-next-line jest/no-conditional-expect
                                expect(currentIndex).toBeLessThan(comparatorIndex);
                            }

                            if (!isCurrentDiscounted && isComparatorDiscounted) {
                                // eslint-disable-next-line jest/no-conditional-expect
                                expect(currentIndex).toBeGreaterThan(comparatorIndex);
                            }
                        } else {
                            // variants are ordered by recommendation
                            if (currentRecommendation < comparatorRecommendation) {
                                // eslint-disable-next-line jest/no-conditional-expect
                                expect(currentIndex).toBeLessThan(comparatorIndex);
                            }

                            if (currentIndex > comparatorRecommendation) {
                                // eslint-disable-next-line jest/no-conditional-expect
                                expect(currentIndex).toBeGreaterThan(comparatorIndex);
                            }
                        }
                    });
                });
        });
    });

    describe('getPriceFromVariant', () => {
        it('returns discounted price if variant is discounted', async () => {
            expect(
                heyframeExtensionService.getPriceFromVariant({
                    netPrice: 100,
                    discountCampaign: {
                        discountedPrice: 80,
                    },
                }),
            ).toBe(80);
        });

        it('returns net price if variant is not discounted', async () => {
            HeyFrame.Service('heyframeDiscountCampaignService').isDiscountCampaignActive.mockImplementationOnce(() => false);

            expect(
                heyframeExtensionService.getPriceFromVariant({
                    netPrice: 100,
                    discountCampaign: {
                        discountedPrice: 80,
                    },
                }),
            ).toBe(100);
        });
    });

    describe('mapVariantToRecommendation', () => {
        it.each([
            [
                'free',
                0,
            ],
            [
                'rent',
                1,
            ],
            [
                'buy',
                2,
            ],
            [
                'test',
                3,
            ],
        ])('maps variant %s to position %d', (type, expectedRecommendation) => {
            expect(heyframeExtensionService.mapVariantToRecommendation({ type })).toBe(expectedRecommendation);
        });
    });

    describe('getOpenLink', () => {
        it('returns always a open link for theme', async () => {
            const themeId = HeyFrame.Utils.createId();

            const responses = global.repositoryFactoryMock.responses;
            responses.addResponse({
                method: 'Post',
                url: '/search-ids/theme',
                status: 200,
                response: {
                    data: [themeId],
                },
            });

            const openLink = await heyframeExtensionService.getOpenLink({
                isTheme: true,
                type: heyframeExtensionService.EXTENSION_TYPES.APP,
                name: 'SwagExampleApp',
            });

            expect(openLink).toEqual({
                name: 'sw.theme.manager.detail',
                params: { id: themeId },
            });
        });

        it('returns valid open link for app with main module', async () => {
            HeyFrame.Store.get('heyframeApps').apps = appModulesFixtures;

            expect(
                await heyframeExtensionService.getOpenLink({
                    isTheme: false,
                    type: heyframeExtensionService.EXTENSION_TYPES.APP,
                    name: 'testAppA',
                }),
            ).toEqual({
                name: 'sw.extension.module',
                params: {
                    appName: 'testAppA',
                },
            });
        });

        it('returns no open link for app without main module', async () => {
            HeyFrame.Store.get('heyframeApps').apps = appModulesFixtures;

            expect(
                await heyframeExtensionService.getOpenLink({
                    isTheme: false,
                    type: heyframeExtensionService.EXTENSION_TYPES.APP,
                    name: 'testAppB',
                }),
            ).toBeNull();
        });

        it('returns no open link if app can not be found', async () => {
            HeyFrame.Store.get('heyframeApps').apps = appModulesFixtures;

            expect(
                await heyframeExtensionService.getOpenLink({
                    isTheme: false,
                    type: heyframeExtensionService.EXTENSION_TYPES.APP,
                    name: 'ThisAppDoesNotExist',
                }),
            ).toBeNull();
        });

        it('returns no open link for plugins not registered', async () => {
            expect(
                await heyframeExtensionService.getOpenLink({
                    isTheme: false,
                    type: heyframeExtensionService.EXTENSION_TYPES.PLUGIN,
                    name: 'SwagNoModule',
                }),
            ).toBeNull();
        });

        it('returns route for plugins registered', async () => {
            expect(
                await heyframeExtensionService.getOpenLink({
                    isTheme: false,
                    type: heyframeExtensionService.EXTENSION_TYPES.PLUGIN,
                    name: 'ExamplePlugin',
                    active: true,
                }),
            ).toEqual({
                label: null,
                name: 'test.foo',
            });
        });
    });
});
