# Adding and combining inline styles for critical CSS in Neos CMS

[![Latest Stable Version](https://poser.pugx.org/shel/critical-css/v/stable)](https://packagist.org/packages/shel/critical-css)
[![License](https://poser.pugx.org/shel/critical-css/license)](https://packagist.org/packages/shel/critical-css)
[![Travis Build Status](https://travis-ci.org/Sebobo/Shel.CriticalCSS.svg?branch=master)](https://travis-ci.org/Sebobo/Shel.CriticalCSS)
[![StyleCI](https://styleci.io/repos/219770230/shield?style=flat)](https://styleci.io/repos/219770230)

_This package is in its early stages and doesn't have a 1.0 release yet_

This package provides several helpers to allow adding (dynamic) inline styles to Fusion components in Neos CMS
and combining them locally or into the head of a html document.

CSS classes are dynamically generated based on the hash of the defined styles.

This allows you to keep styles together with a component in one file and also be able to override them.
A good use case for this is to define inline styles for your critical CSS that should be loaded instantly when
the site is shown and defer loading other styles that are not needed immediately.

Support:

* Scoped styles
* Global styles with specific selectors
* Automatic style merging into document head
* Style nesting
* `@media` and `@supports` queries

The inspiration for this package came from [SvelteJS](https://svelte.dev) and [JSS](https://cssinjs.org).

## Installation

Run this in your site package:

    composer require --no-update shel/critical-css
    
Then run `composer update` in your project root.
                                        
## Usage

### Adding inline styles

Given the following example component:

    prototype(My.Site:Component.Blockquote) < prototype(Neos.Fusion:Component) {
        text = ''
        
        renderer = afx`
            <blockquote>{props.text}</blockquote>
        `
        
        @process.styles = Shel.CriticalCSS:Styles {
            font-size = '2em'
            font-family = 'Comic Sans'
            padding = '.5rem'
        }
    }
    
The resulting HTML will look similar to this:

    <style data-inline>.style--cd7c679e3d{font-size:2m;font-family:Comic Sans;padding:.5rem;}</style>
    <blockquote class="style--cd7c679e3d">My text</blockquote>
    
This will already work but you will have a lot of style tags this way and possible duplicates.

This package automatically applies a "style collector" to the document which collects all these inline styles.
So all style tags will first collected, then merged into one list, duplicates removed and inserted into the HTML head
as one style tag.      

Be careful when applying the styles not only to one tag but to a group of tags. Then it will
automatically generate a wrapping tag similar to how the Augmenter in Fusion works.

#### Nesting styles

Again a similar example but with nested styles

    prototype(My.Site:Component.Blockquote) < prototype(Neos.Fusion:Component) {
        text = ''
        link = ''
        
        renderer = afx`
            <blockquote>
                {props.text} 
                <a href={props.link}>Read more</a>
            </blockquote>
        `
        
        @process.styles = Shel.CriticalCSS:Styles {
            font-size = '2em'
            font-family = 'Comic Sans'
            padding = '.5rem'
            a {
                color = 'blue'
                text-decoration = 'underline'
            }
        }
    }                         
    
The resulting HTML will look similar to this:

    <style data-inline>.style--cd7c679e3d{font-size:2m;font-family:Comic Sans;padding:.5rem;}.style--cd7c679e3d a{color:blue;text-decoration:underline;}</style>
    <blockquote class="style--cd7c679e3d">My text</blockquote>

#### Using multiple styles in one component

To style several parts of a component you can do the following:

    prototype(My.Site:Component.Complex) < prototype(Neos.Fusion:Component) {
        headline = ''
        text = ''
        footer = ''
        
        renderer = afx`
            <section>
                <header>
                    <Shel.CriticalCSS:Styles @path="@process.styles"
                        font-weight="bold"
                        color="black"
                    />
                    {props.headline}
                </header>
                
                <div>{props.text}</div>
                
                <footer>
                    <Shel.CriticalCSS:Styles @path="@process.styles"
                        font-size="80%"
                        color="gray"
                    />
                    {props.footer}
                </footer>
            </section>
        `
        
        @process.styles = Shel.CriticalCSS:Styles {
            padding = '.5rem'
        }
    }    
    
This will result in three style tags with three different css classes. All of them will be picked up 
by the collector.

#### Modifying styles with props

This method allows you to modify styles easily by using props:

    prototype(My.Site:Component.TextBanner) < prototype(Neos.Fusion:Component) {
        text = ''
        backgroundColor = '#4444ff'        
        
        renderer = afx`
            <div>
                <Shel.CriticalCSS:Styles @path="@process.styles"
                    background-color={props.backgroundColor}
                />
                {props.text}
            </div>
        `
    }   
    
Remember you can only access the props when the styles object is applied somewhere inside the `renderer`.
You can do this either via `renderer.@process.styles` or like in the example code.
    
#### Usage with CSS variables

You can also easily define global or local CSS variables this way:

    prototype(My.Site:Document.Page) < prototype(Neos.Neos:Page) {
        body {
            content {
                main = Neos.Neos:PrimaryContent {
                    nodePath = 'main'
                    
                    @process.styles = Shel.CriticalCSS:Styles {
                        --theme-background = ${q(site).property('themeBackground')}
                        --theme-color = ${q(site).property('themeColor')}
                    }
                }
            }
        }
    } 
    
And when you then have a nested component like this:    
 
    prototype(My.Site:Component.Blockquote) < prototype(Neos.Fusion:Component) {
        text = ''
        
        renderer = afx`
            <blockquote>{props.text}</blockquote>
        `
        
        @process.styles = Shel.CriticalCSS:Styles {
            color = 'var(--theme-color)'
        }
    }
    
I can recommend to use my [colorpicker](https://github.com/Sebobo/Shel.Neos.ColorPicker) for Neos CMS when
allowing an editor to define a theme and then put those values into CSS variables. 

#### Custom selectors

It's also possible to use custom selectors to target the `html`, `body` or all tags `*`:
 
    prototype(Neos.Neos:Page) {
        body {                  
            @process.globalStyles = Neos.Fusion:Join {
                all = Shel.CriticalCSS:Styles {
                    selector = '*'
                    box-sizing = 'border-box'
                }
                body = Shel.CriticalCSS:Styles {
                    selector = 'body'
                    background-color = 'blue'
                }
            }
        }
    }

#### Other examples

You can also take a look at the [functional test fixtures](Tests/Functional/Fixtures/Fusion/Styles.fusion) to see the verified use cases.
               
### Modifying the collector behaviour

By default the collector is applied as `process` to `Neos.Neos:Page`. You can disable this and instead
apply it to any other prototype. The collector will then do the same thing as before and collect all style tags
but now prepend all of them as one style tag to the prototype the collector was applied to.
 
### Limitations

#### Inserting new elements in the Neos UI

When working the backend the styles collector will not automatically pick up style blocks from newly inserted elements.
This might cause some issues when the added elements are somehow treated with Javascript like in Sliders.

#### Caching

The styles collector might not always pick up dynamically cached elements as they will add their own styleblock
without triggering a refresh of the page cache.
