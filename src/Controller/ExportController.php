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

namespace BugBuster\DlstatsExportBundle\Controller;

use Contao\Config;
use Contao\CoreBundle\Translation\Translator;
use Contao\Date;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use PDO;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao/dlstats/export", defaults={"_scope" = "backend", "_token_check" = true})
 */
class ExportController extends AbstractController
{
    /**
     * @var false|int
     */
    private $exportFrom;

    /**
     * @var false|int
     */
    private $exportTo;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Worksheet
     */
    private $sheet;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @Route("/{year}/{month}/{format}", name="bugbuster_dlstats_export")
     *
     * @param $year
     * @param $month
     * @param $format
     *
     * @throws Exception
     *
     * @return BinaryFileResponse
     */
    public function onExport($year, $month, $format)
    {
        $this->connection = $this->get('database_connection');
        $this->translator = $this->get('translator');

        if ('all' !== $year) {
            $this->exportFrom = mktime(0, 0, 0, 1, 1, (int) $year);
            $this->exportTo = mktime(23, 59, 59, 12, 31, (int) $year);
        }

        if ('all' === $year && 'all' !== $month) {
            $year = \date('Y');
        }

        if ('all' !== $month && 'all' !== $year) {
            $this->exportFrom = mktime(0, 0, 0, (int) $month, 1, (int) $year);
            $this->exportTo = mktime(23, 59, 59, (int) $month + 1, 0, (int) $year);
        }

        $path = $this->get('kernel')->getCacheDir().'/contao/dlstats';
        $fileName = 'export_'.Date::parse('Y-m-d_His', time()).'.'.$format;
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($path)) {
            $fileSystem->mkdir($path);
        }

        $spreadsheet = new Spreadsheet();
        $this->sheet = $spreadsheet->getActiveSheet();

        $this->onGenerateExportData();

        $writer = IOFactory::createWriter($spreadsheet, ucfirst($format));
        $writer->save($path.'/'.$fileName);

        $response = new BinaryFileResponse($path.'/'.$fileName);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * @throws Exception
     */
    private function onGenerateExportData(): void
    {
        $downloads = $this->connection->createQueryBuilder()
            ->select(
                [
                    'tl_dlstats.filename',
                    'tl_dlstatdets.tstamp',
                    'tl_dlstatdets.ip',
                    'tl_dlstatdets.username',
                    'tl_dlstatdets.domain',
                    'tl_dlstatdets.page_host',
                    'tl_dlstatdets.page_id',
                    'tl_dlstatdets.browser_lang',
                ]
            )
            ->from('tl_dlstats')
            ->innerJoin('tl_dlstats', 'tl_dlstatdets', 'tl_dlstatdets', 'tl_dlstats.id=tl_dlstatdets.pid')
            ->addOrderBy('tl_dlstatdets.tstamp')
            ->addOrderBy('tl_dlstats.filename')
        ;

        if (null !== $this->exportFrom) {
            $downloads
                ->andWhere('tl_dlstatdets.tstamp BETWEEN :exportFrom AND :exportTo')
                ->setParameter('exportFrom', $this->exportFrom)
                ->setParameter('exportTo', $this->exportTo)
            ;
        }

        $downloads = $downloads->execute()->fetchAll(PDO::FETCH_OBJ);

        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);
        $this->sheet->getColumnDimension('E')->setAutoSize(true);
        $this->sheet->getColumnDimension('F')->setAutoSize(true);
        $this->sheet->getColumnDimension('G')->setAutoSize(true);
        $this->sheet->getColumnDimension('H')->setAutoSize(true);

        $this->sheet->getStyle('A1:H1')->applyFromArray(
            [
                'font' => [
                    'bold' => true,
                ],
            ]
        );

        $this->sheet
            ->setCellValue('A1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.filename'))
            ->setCellValue('B1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.date'))
            ->setCellValue('C1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.ip'))
            ->setCellValue('D1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.username'))
            ->setCellValue('E1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.domain'))
            ->setCellValue('F1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.page_host'))
            ->setCellValue('G1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.page_alias'))
            ->setCellValue('H1', $this->translator->trans('bugbuster.dlstat.export.sheet.header.browser_lang'))
        ;

        if (empty($downloads)) {
            return;
        }

        $row = 1;

        foreach ($downloads as $download) {
            ++$row;
            $this->sheet->setCellValue('A'.$row, $download->filename);
            $this->sheet->setCellValue('B'.$row, Date::parse(Config::get('datimFormat'), $download->tstamp));
            $this->sheet->setCellValue('C'.$row, $download->ip);
            $this->sheet->setCellValue('D'.$row, $download->username);
            $this->sheet->setCellValue('E'.$row, $download->domain);
            $this->sheet->setCellValue('F'.$row, $download->page_host);
            $this->sheet->setCellValue('G'.$row, $this->getPageAlias($download->page_id));
            $this->sheet->setCellValue('H'.$row, $download->browser_lang);

            //$row++;
        }
    }

    /**
     * @param $id
     *
     * @return string
     */
    private function getPageAlias($id)
    {
        if (0 === (int) $id) {
            return '';
        }

        $objPage = PageModel::findByPk($id);

        if (null === $objPage) {
            return $id;
        }

        return $objPage->alias;
    }
}
