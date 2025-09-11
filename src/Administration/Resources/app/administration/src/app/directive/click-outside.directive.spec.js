/**
 * @sw-package framework
 */
describe('directives/click-outside', () => {
    it('should register the directive', () => {
        expect(HeyFrame.Directive.getByName('click-outside')).toBeDefined();
    });
});
