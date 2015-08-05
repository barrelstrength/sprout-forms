<?php
namespace Craft;

class SproutReportsSproutFormsIntegration extends SproutReportsBaseReport
{
	protected $name;
	protected $query;
	protected $group='Sprout Forms';
	protected $userOptions=array();
	protected $description='';

	/*
	* Set report group
	* @param $group string
    * @return void
	*/
	public function setGroup($group)
	{
		$this->group=$group;
	}

	/*
	* Report group
    * @return string
	*/
	public function getGroup()
	{
		return $this->group;
	}

	/*
	 * Set report name
	 * @param $name string
	 * @return void
	 */
	public function setName($name)
	{
		$this->name=$name;
	}

	/*
	 * Report name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/*
	 * Set report description
	 * @param $name string
	 * @return void
	 */
	public function setDescription($description)
	{
		$this->description=$description;
	}

	/*
	 * Report description
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/*
	 * Set SQL query to be used for generating reports
	 * @param $query string
	 * @return void
	 */
	public function setQuery($query)
	{
		$this->query=$query;
	}

	/*
	 * SQL query to be used for generating reports
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/*
	 * Set user options for report
	 * @return array
	 */
	public function setUserOptions(array $userOptions)
	{
		$this->userOptions=$userOptions;
	}

	/*
	 * User options for report
	 * @return array
	 */
	public function getUserOptions()
	{
		return $this->userOptions;
	}

	/*
	 * Create ReportBase from SproutForm object
	 * @param SproutForms
	 */

	public static function createReport($form)
	{
		$report = new SproutReportsSproutFormsIntegration();
		$report->setName($form->name);
		$report->setQuery('SELECT * FROM {{sproutformscontent_'.$form->handle.'}}');
        $userOptions = array();
        $userOptions['dateCreatedFrom'] = array(
            'type' => 'date',
            'column' => 'dateCreated',
            'name' => 'Creation date: from',
            'comparisonOperator' => '>=',
            'showDate' => true,
            'showTime' =>  true
        );
        $userOptions['dateCreatedTill'] = array(
            'type' => 'date',
            'column' => 'dateCreated',
            'name' => 'Creation date: till',
            'comparisonOperator' => '<',
            'showDate' => true,
            'showTime' =>  true
        );
        $formFields = $form->getFieldLayout()->getFields();
        foreach ($formFields as $field)
        {
            $field = $field->getField();
            switch ($field->type)
            {
                case 'Dropdown':
                    $fieldOptions = array();
                    foreach ($field['settings']['options'] as $option)
                    {
                        if ($option['value'])
                        {
                            $fieldOptions[] = array(
                                'label' => $option['label'],
                                'value' => $option['value']
                            );
                        }
                    }

                    if (count($fieldOptions))
                    {
                        $userOptions['field_'.$field['handle']] = array(
                            'type' => 'dropdown',
                            'column' => 'field_'.$field['handle'],
                            'name' => $field['name'],
                            'comparisonOperator' => '=',
                            'values' => $fieldOptions
                        );
                    }
                    break;
            }
        }

        $report->setUserOptions($userOptions);
		return $report;
	}
}