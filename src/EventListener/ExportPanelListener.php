<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions)
 * @package    Contao Download Statistics Bundle (Dlstats) Add-on: Statistic Export
 * @link       https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace BugBuster\DlstatsExportBundle\EventListener;

use BugBuster\DlstatsExportBundle\Form\Type\RequestTokenType;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * ExportPanelListener constructor.
     */
    public function __construct(Connection $connection, ContainerInterface $container, RequestStack $requestStack, FormFactoryInterface $formFactory)
    {
        $this->connection = $connection;
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
    }

    /**
     * @return string|RedirectResponse
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onGetPanelLine()
    {
        $years = $this->connection->createQueryBuilder()
            ->select("DISTINCT(FROM_UNIXTIME(tstamp,'%Y')) AS year")
            ->from('tl_dlstatdets')
            ->orderBy('year', 'DESC')
            ->executeQuery()
            ->fetchFirstColumn()
        ;

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $labelfunc = static fn ($value) => 'bugbuster.dlstat.export.form.labels.'.$value;
        $monthlabels = array_map($labelfunc, range(1, 12));

        // $form = $this->container->get('form.factory')->createNamedBuilder('export',
        // FormType::class)->getForm() ;
        $form = $this->formFactory->createNamedBuilder('export', FormType::class)->getForm();
        $form
            ->add(
                'REQUEST_TOKEN',
                RequestTokenType::class,
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
                        ],
                    ),
                ],
            )
            ->add(
                'month',
                ChoiceType::class,
                [
                    'label' => 'bugbuster.dlstat.export.form.labels.month',
                    'required' => true,
                    'choices' => array_combine(array_merge(['bugbuster.dlstat.export.form.labels.all'], $monthlabels), array_merge(['all'], $months)),
                    'attr' => [
                        'class' => 'tl_select',
                    ],
                    'constraints' => new Choice(
                        [
                            'choices' => array_merge(['all'], $months),
                        ],
                    ),
                ],
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
                    'choice_translation_domain' => false,
                    'constraints' => new Choice(
                        [
                            'choices' => [
                                'xlsx',
                                'csv',
                            ],
                        ],
                    ),
                ],
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'bugbuster.dlstat.export.form.buttons.export',
                    'attr' => [
                        'class' => 'tl_submit',
                    ],
                ],
            )
        ;

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $url = $this->container->get('router')->generate('bugbuster_dlstats_export', [
                'year' => $data['year'],
                'month' => $data['month'],
                'format' => $data['format'],
            ]);

            $response = new RedirectResponse($url);
            $response->send();
        }

        return $this->container->get('twig')->render('@BugBusterContaoDlstatsExport/backend/export_panel.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
