<?php
namespace Craft;

class SproutForms_EntryStatusModel extends BaseModel
{
	/**
	 * @return string
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('sproutForms/settings/orderstatuses/' . $this->id);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->name;
	}

	/**
	 * @return string
	 */
	public function htmlLabel()
	{
		return sprintf('<span class="sproutFormsStatusLabel"><span class="status %s"></span> %s</span>',
			$this->color, $this->name);
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'id'        => AttributeType::Number,
			'name'      => array(AttributeType::String, 'required' => true),
			'handle'    => array(AttributeType::Handle, 'required' => true),
			'color'     => array(AttributeType::String, 'default' => 'blue'),
			'sortOrder' => array(AttributeType::SortOrder),
			'isDefault' => array(AttributeType::Bool, 'default' => 0)
		);
	}
}