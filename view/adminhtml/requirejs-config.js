var config = {
    map: {
        '*': {
            'tippy': 'Magentiz_ConfigFinder/js/tippy-wrapper',
            '@popperjs/core': 'Magentiz_ConfigFinder/js/lib/popper',
            'searchConfig': 'Magentiz_ConfigFinder/js/search-config',
            'xls_core': 'Magentiz_ConfigFinder/js/lib/xls.core.min',
            'xlsx_core': 'Magentiz_ConfigFinder/js/lib/xlsx.core.min'
        }
    },
    paths: {
        'xlsx': 'Magentiz_ConfigFinder/js/lib/xlsx.full.min'
    },
    config: {
        mixins: {
            'mage/backend/button': {
                'Magentiz_ConfigFinder/js/button-mixin': true
            },
            'Magento_Backend/js/save-with-confirm': {
                'Magentiz_ConfigFinder/js/save-with-confirm-mixin': true
            }
        }
    }
};
