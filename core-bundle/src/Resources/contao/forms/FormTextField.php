<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Class FormTextField
 *
 * @property string  $value
 * @property string  $type
 * @property integer $maxlength
 * @property boolean $mandatory
 * @property integer $min
 * @property integer $max
 * @property integer $step
 * @property string  $placeholder
 * @property boolean $hideInput
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @todo Rename to FormText in Contao 5.0
 */
class FormTextField extends Widget
{
	/**
	 * Submit user input
	 *
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Add a for attribute
	 *
	 * @var boolean
	 */
	protected $blnForAttribute = true;

	/**
	 * Template
	 *
	 * @var string
	 *
	 * @todo Rename to form_text in Contao 5.0
	 */
	protected $strTemplate = 'form_textfield';

	/**
	 * The CSS class prefix
	 *
	 * @var string
	 */
	protected $strPrefix = 'widget widget-text';

	/**
	 * Add specific attributes
	 *
	 * @param string $strKey   The attribute key
	 * @param mixed  $varValue The attribute value
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'minlength':
				if ($varValue > 0 && $this->rgxp != 'digit')
				{
					$this->arrAttributes['minlength'] =  $varValue;
				}
				break;

			case 'maxlength':
				if ($varValue > 0 && $this->rgxp != 'digit')
				{
					$this->arrAttributes['maxlength'] =  $varValue;
				}
				break;

			case 'mandatory':
				if ($varValue)
				{
					$this->arrAttributes['required'] = 'required';
				}
				else
				{
					unset($this->arrAttributes['required']);
				}
				parent::__set($strKey, $varValue);
				break;

			case 'min':
			case 'minval':
				if ($this->rgxp == 'digit')
				{
					$this->arrAttributes['min'] = $varValue;
				}
				break;

			case 'max':
			case 'maxval':
				if ($this->rgxp == 'digit')
				{
					$this->arrAttributes['max'] = $varValue;
				}
				break;

			case 'step':
				if ($varValue > 0 && $this->type == 'number')
				{
					$this->arrAttributes[$strKey] = $varValue;
				}
				else
				{
					unset($this->arrAttributes[$strKey]);
				}
				break;

			case 'placeholder':
				$this->arrAttributes[$strKey] = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}

	/**
	 * Return a parameter
	 *
	 * @param string $strKey The parameter key
	 *
	 * @return mixed The parameter value
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'value':
				// Hide the Punycode format (see #2750)
				if ($this->rgxp == 'url')
				{
					try
					{
						return Idna::decodeUrl($this->varValue);
					}
					catch (\InvalidArgumentException $e)
					{
						return $this->varValue;
					}
				}
				elseif ($this->rgxp == 'email' || $this->rgxp == 'friendly')
				{
					return Idna::decodeEmail($this->varValue);
				}

				return $this->varValue;

			case 'type':
				if ($this->hideInput)
				{
					return 'password';
				}

				// Use the HTML5 types (see #4138) but not the date, time and datetime types (see #5918)
				switch ($this->rgxp)
				{
					case 'digit':
						// Allow floats (see #7257)
						if (!isset($this->arrAttributes['step']))
						{
							$this->addAttribute('step', 'any');
						}
						// no break

					case 'natural':
						return 'number';

					case 'phone':
						return 'tel';

					case 'email':
						return 'email';

					case 'url':
						return 'url';
				}

				return 'text';

			default:
				return parent::__get($strKey);
		}
	}

	/**
	 * Trim the values
	 *
	 * @param mixed $varInput The user input
	 *
	 * @return mixed The validated user input
	 */
	protected function validator($varInput)
	{
		if (\is_array($varInput))
		{
			return parent::validator($varInput);
		}

		// Convert to Punycode format (see #5571)
		if ($this->rgxp == 'url')
		{
			try
			{
				$varInput = Idna::encodeUrl($varInput);
			}
			catch (\InvalidArgumentException $e)
			{
			}
		}
		elseif ($this->rgxp == 'email' || $this->rgxp == 'friendly')
		{
			$varInput = Idna::encodeEmail($varInput);
		}

		return parent::validator($varInput);
	}

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string The widget markup
	 */
	public function generate()
	{
		return sprintf(
			'<input type="%s" name="%s" id="ctrl_%s" class="text%s%s" value="%s"%s%s',
			$this->type,
			$this->strName,
			$this->strId,
			($this->hideInput ? ' password' : ''),
			($this->strClass ? ' ' . $this->strClass : ''),
			StringUtil::specialchars($this->value),
			$this->getAttributes(),
			$this->strTagEnding
		);
	}
}

class_alias(FormTextField::class, 'FormTextField');
