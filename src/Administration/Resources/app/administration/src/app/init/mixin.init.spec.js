/**
 * @sw-package framework
 */
import createAppMixin from 'src/app/init/mixin.init';

describe('src/app/init/mixin.init.js', () => {
    it('should register all app mixins', () => {
        createAppMixin();

        expect(HeyFrame.Mixin.getByName('sw-form-field')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('generic-condition')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('listing')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('notification')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('placeholder')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('position')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('remove-api-error')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('ruleContainer')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('salutation')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('sw-inline-snippet')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('user-settings')).toBeDefined();
        expect(HeyFrame.Mixin.getByName('validation')).toBeDefined();
    });
});
