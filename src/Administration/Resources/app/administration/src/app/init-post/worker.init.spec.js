/**
 * @sw-package framework
 */
import WorkerNotificationFactory from 'src/core/factory/worker-notification.factory';
import { createPinia, setActivePinia } from 'pinia';
import initializeWorker from './worker.init';

describe('src/app/init-post/worker.init.ts', () => {
    let loggedIn = false;
    let config = {};
    let loginListeners = [];

    beforeAll(() => {
        HeyFrame.Service().register('loginService', () => {
            return {
                isLoggedIn: () => {
                    return loggedIn;
                },

                addOnLoginListener: (listener) => {
                    loginListeners.push(listener);
                },
                getBearerAuthentication: () => {
                    return 'jest';
                },
                refreshToken: () => {
                    return Promise.resolve();
                },

                addOnTokenChangedListener: () => {},
                addOnLogoutListener: () => {},
            };
        });

        HeyFrame.Service().register('configService', () => {
            return {
                getConfig: () => {
                    return Promise.resolve(config);
                },
            };
        });
    });

    beforeEach(() => {
        const registry = WorkerNotificationFactory.getRegistry();
        registry.clear();

        WorkerNotificationFactory.resetHelper();

        setActivePinia(createPinia());
    });

    afterEach(() => {
        loggedIn = false;
        config = {};
        loginListeners = [];
    });

    it('should not initialize if not logged in', () => {
        loggedIn = false;

        initializeWorker();

        expect(loginListeners).toHaveLength(1);
    });

    it.each([
        'HeyFrame\\Core\\Framework\\DataAbstractionLayer\\Indexing\\MessageQueue\\IndexerMessage',
        'HeyFrame\\Elasticsearch\\Framework\\Indexing\\IndexingMessage',
        'HeyFrame\\Core\\Content\\Media\\Message\\GenerateThumbnailsMessage',
        'HeyFrame\\Core\\Checkout\\Promotion\\DataAbstractionLayer\\PromotionIndexingMessage',
        'HeyFrame\\Core\\Content\\ProductStream\\DataAbstractionLayer\\ProductStreamIndexingMessage',
        'HeyFrame\\Core\\Content\\Category\\DataAbstractionLayer\\CategoryIndexingMessage',
        'HeyFrame\\Core\\Content\\Media\\DataAbstractionLayer\\MediaIndexingMessage',
        'HeyFrame\\Core\\System\\SalesChannel\\DataAbstractionLayer\\SalesChannelIndexingMessage',
        'HeyFrame\\Core\\Content\\Rule\\DataAbstractionLayer\\RuleIndexingMessage',
        'HeyFrame\\Core\\Content\\Product\\DataAbstractionLayer\\ProductIndexingMessage',
        'HeyFrame\\Elasticsearch\\Framework\\Indexing\\ElasticsearchIndexingMessage',
        'HeyFrame\\Core\\Content\\ImportExport\\Message\\ImportExportMessage',
        'HeyFrame\\Core\\Content\\Flow\\Indexing\\FlowIndexingMessage',
        'HeyFrame\\Core\\Content\\Newsletter\\DataAbstractionLayer\\NewsletterRecipientIndexingMessage',
    ])('should register thumbnail middleware "%s"', async (name) => {
        loggedIn = true;

        config = {
            adminWorker: {
                enableQueueStatsWorker: false,
            },
        };

        initializeWorker();
        const helper = WorkerNotificationFactory.initialize();

        const createMock = jest.fn(() => {
            return Promise.resolve('jest-id');
        });

        helper.go({
            queue: [
                { name, size: 1 },
            ],
            $root: {
                $tc: (msg) => msg,
            },
            notification: {
                create: createMock,
            },
        });

        await flushPromises();

        expect(loginListeners).toHaveLength(0);
        expect(createMock).toHaveBeenCalledTimes(1);
    });

    it('should update thumbnail middleware notifications', async () => {
        loggedIn = true;

        config = {
            adminWorker: {
                enableQueueStatsWorker: false,
            },
        };

        initializeWorker();
        const helper = WorkerNotificationFactory.initialize();

        const createMock = jest.fn(() => {
            return Promise.resolve('jest-id');
        });

        const updateMock = jest.fn(() => {
            return Promise.resolve();
        });

        // First run should create notification
        helper.go({
            queue: [
                {
                    name: 'HeyFrame\\Core\\Framework\\DataAbstractionLayer\\Indexing\\MessageQueue\\IndexerMessage',
                    size: 1,
                },
            ],
            $root: {
                $tc: (msg) => msg,
            },
            notification: {
                create: createMock,
                update: updateMock,
            },
        });
        await flushPromises();
        expect(createMock).toHaveBeenCalledTimes(1);

        // Second run should update notification
        helper.go({
            queue: [
                {
                    name: 'HeyFrame\\Core\\Framework\\DataAbstractionLayer\\Indexing\\MessageQueue\\IndexerMessage',
                    size: 0,
                },
            ],
            $root: {
                $t: (msg) => msg,
                $tc: (msg) => msg,
            },
            notification: {
                create: createMock,
                update: updateMock,
            },
        });
        await flushPromises();
        expect(updateMock).toHaveBeenCalledTimes(1);
    });

    it('should update config if logged in', async () => {
        loggedIn = true;

        config = {
            version: 'jest',
            adminWorker: {
                enableQueueStatsWorker: false,
            },
        };

        initializeWorker();
        await flushPromises();

        expect(loginListeners).toHaveLength(0);
        expect(HeyFrame.Store.get('context').app.config.version).toBe('jest');
    });
});
