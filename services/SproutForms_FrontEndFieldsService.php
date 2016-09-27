<?php
namespace Craft;

class SproutForms_FrontEndFieldsService extends BaseApplicationComponent
{
	/**
	 * @param array $settings field settings
	 *
	 * @return array
	 */
	public function getFrontEndEntries($settings)
	{
		$entries  = array();

		$criteria = craft()->elements->getCriteria(ElementType::Entry);

		if (is_array($settings['sources']))
		{
			foreach ($settings['sources'] as $source)
			{
				$section = explode(":", $source);
				$pos     = count($entries) + 1;

				if (count($section) == 2)
				{
					$sectionById = craft()->sections->getSectionById($section[1]);

					$criteria->sectionId = $section[1];
					$entries[$pos]['entries'] = $criteria->find();
					$entries[$pos]['section'] = $sectionById;
				}
				else
				{
					if ($section[0] == 'singles')
					{
						$singles = $this->_getSinglesEntries();

						$entries[$pos]['entries'] = $singles;
						$entries[$pos]['singles'] = true;
					}
				}
			}
		}
		else
		{
			if ($settings['sources'] == '*')
			{
				$sections = craft()->sections->getAllSections();

				foreach ($sections as $section)
				{
					$pos = count($entries) + 1;

					if ($section->type != SectionType::Single)
					{
						$sectionById = craft()->sections->getSectionById($section->id);

						$criteria->sectionId = $section->id;
						$entries[$pos]['entries'] = $criteria->find();
						$entries[$pos]['section'] = $sectionById;
					}
				}

				$singles = $this->_getSinglesEntries();
				$pos     = count($entries) + 1;
				$entries[$pos]['entries'] = $singles;
				$entries[$pos]['singles'] = true;
			}
		}

		return $entries;
	}

	/**
	 * @param array $settings field settings
	 *
	 * @return array
	 */
	public function getFrontEndCategories($settings)
	{
		$categories  = array();

		$criteria = craft()->elements->getCriteria(ElementType::Category);

		if (isset($settings['source']))
		{
			$group = explode(":", $settings['source']);
			$pos   = count($categories) + 1;

			if (count($group) == 2)
			{
				$groupById = craft()->categories->getGroupById($group[1]);

				$criteria->groupId = $group[1];
				$categories[$pos]['categories'] = $criteria->find();
				$categories[$pos]['group'] = $groupById;
			}
		}

		return $categories;
	}

	/**
	 * @param array $settings field settings
	 *
	 * @return array
	 */
	public function getFrontEndTags($settings)
	{
		$tags  = array();

		$criteria = craft()->elements->getCriteria(ElementType::Tag);

		if (isset($settings['source']))
		{
			$group = explode(":", $settings['source']);
			$pos   = count($tags) + 1;

			if (count($group) == 2)
			{
				$groupById = craft()->tags->getTagGroupById($group[1]);

				$criteria->groupId = $group[1];
				$tags[$pos]['tags'] = $criteria->find();
				$tags[$pos]['group'] = $groupById;
			}
		}

		return $tags;
	}

	private function _getSinglesEntries()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$sections = craft()->sections->getSectionsByType(SectionType::Single);
		$singles  = array();

		foreach ($sections as $key => $section)
		{
			$criteria->sectionId = $section->id;
			$results = $criteria->find();
			$singles[] = $results[0];
		}

		return $singles;
	}
}
