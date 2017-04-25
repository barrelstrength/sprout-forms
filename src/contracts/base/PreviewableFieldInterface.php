<?php
namespace barrelstrength\sproutforms\contracts\base;

use craft\base\ElementInterface;
/**
 * PreviewableFieldInterface defines the common interface to be implemented by field classes
 * that wish to be previewable on element indexes in the Control Panel.
 */
interface PreviewableFieldInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the HTML that should be shown for this field in Table View.
     *
     * @param mixed            $value   The field’s value
     * @param ElementInterface $element The element the field is associated with
     *
     * @return string The HTML that should be shown for this field in Table View
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string;
}
