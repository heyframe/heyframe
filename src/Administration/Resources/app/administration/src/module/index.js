/**
 * @sw-package framework
 */

const importLogin = () => {
    return import.meta.glob('./sw-login/index!(*.spec).{j,t}s', {
        eager: true,
    });
};

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default async () => {
    const context = await import.meta.glob([
        './*/index!(*.spec).{j,t}s',
        '!./sw-login/index!(*.spec).{j,t}s',
    ]);

    const modules = Object.values(context)
        .reverse()
        .map((module) => module());

    return Promise.all(modules);
};

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export const login = () => {
    let context = importLogin();

    // import login dependencies
    return Object.values(context);
};
