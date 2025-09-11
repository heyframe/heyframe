/**
 * @sw-package framework
 */

describe('src/app/store/sw-bulk-edit.store', () => {
    it('should be able to setIsFlowTriggered', async () => {
        const state = HeyFrame.Store.get('swBulkEdit');

        HeyFrame.Store.get('swBulkEdit').setIsFlowTriggered(true);
        expect(state.isFlowTriggered).toBe(true);

        HeyFrame.Store.get('swBulkEdit').setIsFlowTriggered(false);
        expect(state.isFlowTriggered).toBe(false);
    });

    it('should be able to setOrderDocumentsIsChanged', async () => {
        const state = HeyFrame.Store.get('swBulkEdit');

        HeyFrame.Store.get('swBulkEdit').setOrderDocumentsIsChanged({
            type: 'invoice',
            isChanged: true,
        });
        expect(state.orderDocuments.invoice.isChanged).toBe(true);

        HeyFrame.Store.get('swBulkEdit').setOrderDocumentsIsChanged({
            type: 'invoice',
            isChanged: false,
        });
        expect(state.orderDocuments.invoice.isChanged).toBe(false);
    });
});
