<?php

declare(strict_types=1);

/**
 * LibreDTE: Biblioteca PHP (Núcleo).
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o modificarlo
 * bajo los términos de la Licencia Pública General Affero de GNU publicada
 * por la Fundación para el Software Libre, ya sea la versión 3 de la Licencia,
 * o (a su elección) cualquier versión posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero SIN
 * GARANTÍA ALGUNA; ni siquiera la garantía implícita MERCANTIL o de APTITUD
 * PARA UN PROPÓSITO DETERMINADO. Consulte los detalles de la Licencia Pública
 * General Affero de GNU para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de
 * GNU junto a este programa.
 *
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace libredte\lib\Core\Sii\Dte\Documento\Builder;

use libredte\lib\Core\Helper\Arr;
use libredte\lib\Core\Sii\Dte\Documento\Normalization\DescuentosRecargosNormalizationTrait;
use libredte\lib\Core\Sii\Dte\Documento\Normalization\DetalleNormalizationTrait;
use libredte\lib\Core\Sii\Dte\Documento\Normalization\ImpuestoAdicionalRetencionNormalizationTrait;
use libredte\lib\Core\Sii\Dte\Documento\Normalization\IvaMntTotalNormalizationTrait;
use libredte\lib\Core\Sii\Dte\Documento\NotaCredito;

/**
 * Constructor ("builder") del documento nota de crédito.
 */
class NotaCreditoBuilder extends AbstractDocumentoBuilder
{
    // Traits usados por este "builder".
    use DetalleNormalizationTrait;
    use DescuentosRecargosNormalizationTrait;
    use ImpuestoAdicionalRetencionNormalizationTrait;
    use IvaMntTotalNormalizationTrait;

    /**
     * Clase del documento que este "builder" construirá.
     *
     * @var string
     */
    protected string $documentoClass = NotaCredito::class;

    /**
     * Normaliza los datos con reglas específicas para el tipo de documento.
     *
     * @param array $data Arreglo con los datos del documento a normalizar.
     * @return array Arreglo con los datos normalizados.
     */
    public function applyDocumentoNormalization(array $data): array
    {
        // Completar con nodos por defecto.
        $data = Arr::mergeRecursiveDistinct([
            'Encabezado' => [
                'IdDoc' => false,
                'Emisor' => false,
                'Receptor' => false,
                'RUTSolicita' => false,
                'Totales' => [
                    'MntNeto' => 0,
                    'MntExe' => 0,
                    'TasaIVA' => $this->getTipoDocumento()->getDefaultTasaIVA(),
                    'IVA' => false,
                    'ImptoReten' => false,
                    'IVANoRet' => false,
                    'CredEC' => false,
                    'MntTotal' => 0,
                ],
            ],
        ], $data);

        // Normalizar datos.
        $data = $this->applyDetalleNormalization($data);
        $data = $this->applyDescuentosRecargosNormalization($data);
        $data = $this->applyImpuestoRetenidoNormalization($data);
        $data = $this->applyIvaMntTotalNormalization($data);

        // Corregir monto neto e iva.
        if (!$data['Encabezado']['Totales']['MntNeto']) {
            $data['Encabezado']['Totales']['MntNeto'] = 0;
            $data['Encabezado']['Totales']['TasaIVA'] = false;
        }

        // Entregar los datos normalizados.
        return $data;
    }
}
