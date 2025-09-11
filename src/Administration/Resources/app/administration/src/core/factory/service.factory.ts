/**
 * @sw-package framework
 */

type ServiceObject = {
    get: <SN extends keyof ServiceContainer>(serviceName: SN) => ServiceContainer[SN];
    list: () => (keyof ServiceContainer)[];
    register: typeof HeyFrame.Application.addServiceProvider;
    registerMiddleware: typeof HeyFrame.Application.addServiceProviderMiddleware;
    registerDecorator: typeof HeyFrame.Application.addServiceProviderDecorator;
};

/**
 * Return the ServiceObject (HeyFrame.Service().myService)
 * or direct access the services (HeyFrame.Service('myService')
 */
function serviceAccessor<SN extends keyof ServiceContainer>(serviceName: SN): ServiceContainer[SN];
function serviceAccessor(): ServiceObject;
function serviceAccessor<SN extends keyof ServiceContainer>(serviceName?: SN): ServiceContainer[SN] | ServiceObject {
    if (serviceName) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-return
        return HeyFrame.Application.getContainer('service')[serviceName];
    }

    const serviceObject: ServiceObject = {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-return
        get: (name) => HeyFrame.Application.getContainer('service')[name],
        list: () => HeyFrame.Application.getContainer('service').$list(),
        register: (name, service) => HeyFrame.Application.addServiceProvider(name, service),
        registerMiddleware: (...args) => HeyFrame.Application.addServiceProviderMiddleware(...args),
        registerDecorator: (...args) => HeyFrame.Application.addServiceProviderDecorator(...args),
    };

    return serviceObject;
}

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default serviceAccessor;
