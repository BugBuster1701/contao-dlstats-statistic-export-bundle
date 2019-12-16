<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 * @copyright  Glen Langer 2008..2019 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions) <https://www.kehr-solutions.de>
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 */

namespace BugBuster\DlstatsExportBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RequestTokenType extends HiddenType
{
    /**
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var string
     */
    private $tokenName;

    /**
     * RequestTokenType constructor.
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager, string $tokenName)
    {
        $this->tokenManager = $tokenManager;
        $this->tokenName = $tokenName;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['full_name'] = 'REQUEST_TOKEN';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'data' => $this->tokenManager->getToken($this->tokenName)->getValue(),
                ]
            )
        ;
    }
}
