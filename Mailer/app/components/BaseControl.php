<?php

namespace Remp\MailerModule\Components;

use Kdyby\Autowired\AutowireComponentFactories;
use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{
    use AutowireComponentFactories;
}
