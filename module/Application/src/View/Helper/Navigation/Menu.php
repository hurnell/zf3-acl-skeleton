<?php

/**
 * Class Menu Override Zend\View\Helper\Navigation\Menu for specific requirements of
 * present application.
 *
 * @package     Application\View\Helper\Navigation
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Application\View\Helper\Navigation;

use Zend\View\Helper\Navigation\Menu as ZendMenu;
use Zend\Navigation\Page\AbstractPage;
use Zend\Navigation\AbstractContainer;
use RecursiveIteratorIterator;

/**
 * Class Menu render an HTML menu for the application updated to conform
 * to the specific requirements of present application
 *
 * @package     Application\View\Helper\Navigation
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Menu extends ZendMenu
{

    /**
     * Renders a normal menu (called from {@link renderMenu()}).
     *
     * @param  AbstractContainer $container          container to render
     * @param  string            $ulClass            CSS class for first UL
     * @param  string            $indent             initial indentation
     * @param  int|null          $minDepth           minimum depth
     * @param  int|null          $maxDepth           maximum depth
     * @param  bool              $onlyActive         render only active branch?
     * @param  bool              $escapeLabels       Whether or not to escape the labels
     * @param  bool              $addClassToListItem Whether or not page class applied to <li> element
     * @param  string            $liActiveClass      CSS class for active LI
     * @return string
     */
    protected function renderNormalMenu(
            AbstractContainer $container, $ulClass, $indent, $minDepth, $maxDepth, $onlyActive, $escapeLabels, $addClassToListItem, $liActiveClass
    )
    {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);

        /* @var $escaper \Zend\View\Helper\EscapeHtmlAttr */
        $escaper = $this->view->plugin('escapeHtmlAttr');

        if ($found) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator(
                $container, RecursiveIteratorIterator::SELF_FIRST
        );

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibility
                continue;
            } elseif ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } elseif ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages(!$this->renderInvisible) || is_int($maxDepth) && $foundDepth + 1 > $maxDepth
                        ) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }
                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);
            if ($depth > $prevDepth) {
                // start new ul tag
                if ($ulClass && $depth == 0) {
                    $addClassToListItem = true;
                    $ulClass = ' class="' . $escaper($ulClass) . '"';
                } else if ($depth == 1) {
                    $ulClass = ' class="dropdown-menu" ';
                } else {
                    $ulClass = '';
                }
                $html .= $myIndent . '<ul' . $ulClass . '>' . PHP_EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . PHP_EOL;
                    $html .= $ind . '</ul>' . PHP_EOL;
                }
                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            } else {
                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            }

            // render li tag and page
            $liClasses = [];
            // Is page active?
            if ($isActive) {
                $liClasses[] = $liActiveClass;
            }

            // Add CSS class from page to <li>
            if ($addClassToListItem && $page->getClass()) {
                $liClasses[] = $page->getClass();
            }
            $liClass = empty($liClasses) ? '' : ' class="' . $escaper(implode(' ', $liClasses)) . '"';
            $html .= $myIndent . '    <li' . $liClass . '>' . PHP_EOL
                    . $myIndent . '        ' . $this->htmlify($page, $escapeLabels, $addClassToListItem) . PHP_EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth + 1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                $html .= $myIndent . '    </li>' . PHP_EOL
                        . $myIndent . '</ul>' . PHP_EOL;
            }
            $html = rtrim($html, PHP_EOL);
        }

        return $html;
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty.
     * 
     * @param AbstractPage $page
     * @param type $escapeLabel
     * @param type $addClassToListItem
     * @return string
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true, $addClassToListItem = false)
    {
        // get attribs for element
        $attribs = [
            'id' => $page->getId(),
            'title' => $this->translate($page->getTitle(), $page->getTextDomain()),
        ];
        $properties = $page->getCustomProperties();
        foreach ($properties as $k => $v) {
            $attribs[$k] = $v;
        }
        if ($addClassToListItem === false) {
            $attribs['class'] = $page->getClass();
        }

        $href = $page->getHref();
        if ($href) {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
        } else {
            $element = 'span';
        }

        $html = '<' . $element . $this->htmlAttribs($attribs) . '>';
        if ($page->getLabel() === 'Identity') {
            $label = $this->getPresentUserEmailAddress();
        } else {
            $label = $this->translate($page->getLabel(), $page->getTextDomain());
        }

        if ($escapeLabel === true) {
            /** @var \Zend\View\Helper\EscapeHtml $escaper */
            $escaper = $this->view->plugin('escapeHtml');
            $html .= $escaper($label);
        } else {
            $html .= $label;
        }
        if (array_key_exists('data-toggle', $attribs) && $attribs['data-toggle'] == 'dropdown') {
            $html .= ' <b class="caret"></b>';
        }

        $html .= '</' . $element . '>';
        return $html;
    }

    /**
     * Get logged in user's e-mail address
     * 
     * @uses \AclUser\Permissions\Acl\Acl::joinAclToNavigation() where setDefaultAcl is called on navigation
     * @return string
     */
    protected function getPresentUserEmailAddress()
    {
        if ($this->getAcl()) {
            return $this->getAcl()->getPresentUserEmailAddress();
        }
        return 'e-mail';
    }

}
