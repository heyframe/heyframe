/**
 * @sw-package framework
 */

/* eslint-disable max-len */
module.exports = {
    create(context) {
        return {
            ImportDeclaration(node) {
                const invalidNodeSources = [];
                invalidNodeSources.push(node.source.value.startsWith('@administration/'));

                if (invalidNodeSources.includes(true)) {
                    context.report({
                        loc: node.source.loc.start,
                        message: `\
You can't use imports directly from the HeyFrame Core via "${node.source.value}". \
Use the global HeyFrame object directly instead (https://developer.heyframe.net/docs/guides/plugins/plugins/administration/the-heyframe-object)`,
                    });
                }
            },
        };
    },
};
