const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    darkMode: 'class',
    content: [
        './_site/**/*.html',
    ],
    
    theme: {
        extend: {
            typography: {
                DEFAULT: {
                    css: {
                        lineHeight: '1.5em',
                        h2: {
                            marginBottom: '0.75em',
                            marginTop: '1.5em',
                        },
                        a: {
                            color: '#6366f1',
                            '&:hover': {
                                color: '#4338ca',
                            },
                            textDecoration: 'none'
                        },
                        blockquote: {
                            lineHeight: '1.25em',
                            paddingLeft: '0.75em',
                            'p::before': {
                                content: 'unset',
                            },
                            'p::after': {
                                content: 'unset',
                            },
                        },
                        code: {
                            font: 'unset',
                            backgroundColor: '#80808033',
                            paddingLeft: '4px',
                            paddingRight: '4px',
                            marginLeft: '-2px',
                            marginRight: '1px',
                            borderRadius: '4px',
                            whiteSpace: 'pre',
                        },
                        'code::before': {
                            content: 'unset',
                        },
                        'code::after': {
                            content: 'unset',
                        },
                        pre: {
                            code: {
                                fontFamily: "'Fira Code Regular', Consolas, Monospace, 'Courier New'",
                            }
                        }
                    },
                },
            },
        },
    },
    
    plugins: [
        require('@tailwindcss/typography')
    ],
};
