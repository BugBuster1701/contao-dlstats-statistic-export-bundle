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

namespace BugBuster\DlstatsExportBundle\EventListener;

use BugBuster\DlstatsExportBundle\Form\Type\RequestTokenType;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Choice;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ExportPanelListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ExportPanelListener constructor.
     */
    public function __construct(Connection $connection, ContainerInterface $container, RequestStack $requestStack)
    {
        $this->connection = $connection;
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string|RedirectResponse
     */
    public function onGetPanelLine()
    {
        $years = $this->connection->createQueryBuilder()
            ->select("DISTINCT(FROM_UNIXTIME(tstamp,'%Y')) AS year")
            ->from('tl_dlstatdets')
            ->orderBy('year', 'DESC')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN)
        ;

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $form = $this->container->get('form.factory')->createNamedBuilder('export', FormType::class)
            ->getForm()
        ;

        $form
            ->add(
                'REQUEST_TOKEN',
                RequestTokenType::class
            )
            ->add(
                'year',
                ChoiceType::class,
                [
                    'label' => 'bugbuster.dlstat.export.form.labels.year',
                    'required' => true,
                    'choices' => array_combine(array_merge(['bugbuster.dlstat.export.form.labels.all'], $years), array_merge(['all'], $years)),
                    'attr' => [
                        'class' => 'tl_select',
                    ],
                    'constraints' => new Choice(
                        [
                            'choices' => array_merge(['all'], $years),
                        ]
                    ),
                ]
            )
            ->add(
                'month',
                ChoiceType::class,
                [
                    'label' => 'bugbuster.dlstat.export.form.labels.month',
                    'required' => true,
                    'choices' => array_combine(array_merge(['bugbuster.dlstat.export.form.labels.all'], $months), array_merge(['all'], $months)),
                    'attr' => [
                        'class' => 'tl_select',
                    ],
                    'constraints' => new Choice(
                        [
                            'choices' => array_merge(['all'], $months),
                        ]
                    ),
                ]
            )
            ->add(
                'format',
                ChoiceType::class,
                [
                    'label' => 'bugbuster.dlstat.export.form.labels.format',
                    'required' => true,
                    'choices' => [
                        'XLSX' => 'xlsx',
                        'CSV' => 'csv',
                    ],
                    'attr' => [
                        'class' => 'tl_select',
                    ],
                    'constraints' => new Choice(
                        [
                            'choices' => [
                                'xlsx',
                                'csv',
                            ],
                        ]
                    ),
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'bugbuster.dlstat.export.form.buttons.export',
                    'attr' => [
                        'class' => 'tl_submit',
                    ],
                ]
            )
        ;

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $url = $this->container->get('router')->generate(
                'bugbuster_dlstats_export',
                [
                    'year' => $data['year'],
                    'month' => $data['month'],
                    'format' => $data['format'],
                ]
            );

            $response = new RedirectResponse($url);
            $response->send();
        }

        return $this->container->get('twig')->render(
            '@BugBusterContaoDlstatsExport/backend/export_panel.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
