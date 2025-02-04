const {
    spacing
} = require('tailwindcss/defaultTheme');

const colors = require('tailwindcss/colors');

const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
    theme: {
        extend: {
            screens: {
                'ss': '360px',
                // => @media (min-width: 360px) { ... }
                'sm': '640px',
                // => @media (min-width: 640px) { ... }
                'md': '768px',
                // => @media (min-width: 768px) { ... }
                'lg': '1024px',
                // => @media (min-width: 1024px) { ... }
                'xl': '1280px',
                // => @media (min-width: 1440px) { ... }
                '1xl': '1440px',
                // => @media (min-width: 1280px) { ... }
                '2xl': '1536px',
                // => @media (min-width: 1536px) { ... }
                '3xl': '1920px'
                // => @media (min-width: 1920px) { ... }

            },
            fontFamily: {
                sans: ["Supreme Variable", "Helvetica Neue", "Arial", "sans-serif"]
            },
            colors: {
                primary: {
                    lighter: "var(--primary-color-lighter)",
                    "DEFAULT": "var(--primary-color)",
                    darker: "var(--primary-color-darker)"
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300']
                },
                background: {
                    lighter: "var(--primary-color)",
                    "DEFAULT": "var(--primary-gray-lighter)",
                    darker: "var(--primary-color-darker)"
                },
                green: {
                    lighter: "var(--primary-green-lighter)",
                    "DEFAULT": "var(--primary-green)"
                },
                red: {
                    lighter: "var(--primary-red-lighter)",
                    "DEFAULT": "var(--primary-red)",
                    darker: "var(--primary-red-darker)"
                },
                orange: {
                    ...colors.orange,
                    "DEFAULT": "var(--primary-orange)",
                },
                blue: {
                    lighter: "var(--primary-color-lighter)",
                    "DEFAULT": "var(--primary-color)",
                    darker: "var(--primary-color-darker)"
                },
                black:"var(--primary-black)",
                gray: {
                    lighter: "var(--primary-gray-lighter)",
                    "DEFAULT": "var(--primary-gray)",
                    darker: "var(--primary-gray-darker)"
                },
                cream: "var(--secondary-gray-light-cream)",
                yellow: colors.amber,
                purple: colors.violet,
                "sky-blue":"var(--color-Sky-blue)"
            },
            textColor: {
                primary: {
                    lighter: "var(--primary-gray-darker)",
                    "DEFAULT": "var(--primary-black)",
                    darker: "var(--primary-color-darker)"
                },
                secondary: {
                    lighter: "var(--secondary-gray-lighter)",
                    "DEFAULT": "var(--secondary-gray)",
                    darker: "var(--primary-green-lighter)"
                }
            },
            backgroundColor: {
                primary: {
                    lighter: "var(--primary-color-lighter)",
                    "DEFAULT": "var(--primary-color)",
                    darker: "var(--primary-color-darker)"
                },
                secondary: {
                    lighter: "var(--secondary-gray-light-cream)",
                    "DEFAULT": "var(--secondary-gray-lighter)",
                    darker: "var(--secondary-gray)"
                },
                container: {
                    lighter: '#ffffff',
                    "DEFAULT": '#fafafa',
                    darker: '#f5f5f5'
                }
            },
            backgroundImage: {
                'custom-gradient': 'linear-gradient(180deg, rgba(255, 255, 255, 0.1) 38%, rgba(255, 255, 255, 0.4) 42%, rgba(255, 255, 255, 0.8) 120%)',
                'product-tabs-background': "url('../images/product-tabs-background.png')",
                'product-tabs-ul-circle': "url('../svg/ul-circle.svg')",
                'product-tabs-ul-default': "url('../svg/ul-default.svg')",
                'product-tabs-download-icon': "url('../svg/download.svg')",
                'product-tabs-hover-download-icon': "url('../svg/download-hover.svg')",
            },
            borderColor: {
                primary: {
                    lighter: "var(--primary-color)",
                    "DEFAULT": "var(--secondary-gray-lighter)",
                    darker: "var(--primary-color-darker)"
                },
                secondary: {
                    lighter: "var(--secondary-gray-light-cream)",
                    "DEFAULT": "var(--secondary-gray-lighter)",
                    darker: "var(--secondary-gray)"
                },
                container: {
                    lighter: '#f5f5f5',
                    "DEFAULT": '#e7e7e7',
                    darker: '#b6b6b6'
                },
                red: {
                    lighter: "var(--primary-red-lighter)",
                    "DEFAULT": "var(--primary-red)",
                    darker: "var(--primary-red-darker)"
                }
            },
            minWidth: {
                8: spacing["8"],
                20: spacing["20"],
                40: spacing["40"],
                48: spacing["48"]
            },
            maxWidth: {
                'unset': 'unset',
            },
            minHeight: {
                14: spacing["14"],
                a11y: '44px',
                'screen-25': '25vh',
                'screen-50': '50vh',
                'screen-75': '75vh'
            },
            maxHeight: {
                '0': '0',
                'screen-25': '25vh',
                'screen-50': '50vh',
                'screen-75': '75vh'
            },
            container: {
                center: true,
                padding: '1.5rem'
            },
            content: {
                'arrow': 'url("../images/arrow.svg")',
            },
            letterSpacing: {
                'sm': '-1px', // Custom negative letter-spacing
            },
        }
    },
    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
    // Examples for excluding patterns from purge
    content: [
        // this theme's phtml and layout XML files
        '../../**/*.phtml',
        '../../*/layout/*.xml',
        '../../*/page_layout/override/base/*.xml',
        // parent theme in Vendor (if this is a child-theme)
        '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
        '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml',
        '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml',
        // app/code phtml files (if need tailwind classes from app/code modules)
        '../../../../../../../app/code/**/*.phtml',
    ]
});
