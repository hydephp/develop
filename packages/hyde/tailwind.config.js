const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    darkMode: 'class',
    content: [
        './_pages/**/*.blade.php',
        './resources/views/**/*.blade.php',
        './vendor/hyde/framework/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            typography: {
                DEFAULT: {
                    css: {
                        lineHeight: '1.5em',
                        maxWidth: '96ch',
                        h2: {
                            marginBottom: '0.75em',
                            marginTop: '1.5em',
                        },
                        a: {
                            color: '#5956eb',
                            '&:hover': {
                                color: '#4f46e5',
                            },
                            textDecoration: 'none'
                        },
                        blockquote: {
                            backgroundColor: '#80808020',
                            borderLeftColor: '#d1d5db',
                            color: 'unset',
                            fontWeight: 500,
                            fontStyle: 'unset',
                            lineHeight: '1.25em',
                            paddingLeft: '0.75em',
                            paddingTop: '.25em',
                            paddingBottom: '.25em',
                            marginTop: '1em',
                            marginBottom: '1em',
                            p: {
                                paddingRight: '.25em',
                                marginTop: '.25em',
                                marginBottom: '.25em',
                            },
                            'p::before': {
                                content: 'unset',
                            },
                            'p::after': {
                                content: 'unset',
                            },
                        },
                        'code:not(pre code)': {
                            font: 'unset',
                            backgroundColor: '#80808033',
                            paddingLeft: '4px',
                            paddingRight: '4px',
                            marginLeft: '-2px',
                            marginRight: '1px',
                            borderRadius: '4px',
                            maxWidth: '80vw',
                            overflowX: 'auto',
                            verticalAlign: 'top',
                            wordBreak: 'break-all'
                        },
                        'code::before': {
                            content: 'unset',
                        },
                        'code::after': {
                            content: 'unset',
                        },
                        pre: {
                            backgroundColor: '#292D3E',
                            borderRadius: '0.25rem',
                            marginTop: '1rem',
                            marginBottom: '1rem',
                            overflowX: 'auto',
                            code: {
                                fontFamily: "'Fira Code Regular', Consolas, Monospace, 'Courier New'",
                            },
                        },
                    },
                },
                invert: {
                    css: {
                        a: {
                            color: '#818cf8',
                            '&:hover': {
                                color: '#6366f1',
                            },
                        },
                    },
                },
            },
            colors: {
                indigo: {
                    500: '#5956eb',
                }
            },
        },
    },

    safelist: [
        'prose',
        'dark:prose-invert',
        'prose-img:inline',
        'text-left',
        'text-center',
        'text-right',
        'ml-auto',
        'mx-auto',
        'mr-auto',
        'my-0',
        'my-4',
        'my-8',
        'py-0',
        'py-4',
        'py-8',
        'mx-0',
        'mx-4',
        'mx-8',
        'px-0',
        'px-4',
        'px-8',
    ],

    plugins: [
        require('@tailwindcss/typography')
    ],
};
