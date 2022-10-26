<?php
namespace Magenest\ViewStock\Api\Data;

interface SourceAreaInterface
{
    const AREA_NAME = "area_name";
    const AREA_CODE = "area_code";
    const QTY = "qty";

    /**
     * @return string
     */
    public function getAreaName(): string;

    /**
     * @param string $areaName
     * @return SourceAreaInterface
     */
    public function setAreaName(string $areaName): SourceAreaInterface;

    /**
     * @return string
     */
    public function getAreaCode(): string;

    /**
     * @param string $areaCode
     * @return SourceAreaInterface
     */
    public function setAreaCode(string $areaCode): SourceAreaInterface;

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @param float $qty
     * @return SourceAreaInterface
     */
    public function setQty(float $qty): SourceAreaInterface;
}
