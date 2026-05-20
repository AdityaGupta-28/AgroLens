<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getDashboardKpis(?int $regionId = null, ?string $state = null, ?string $season = null, ?int $year = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHoldingDistribution(?int $regionId = null, ?string $state = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getIrrigationBreakdown(?int $regionId = null, ?string $state = null, ?string $irrigationSource = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCropDistribution(?int $regionId = null, ?string $state = null, ?string $season = null, ?int $year = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getWellDepthByRegion(?int $regionId = null, ?string $state = null): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMapMarkers(?string $state = null, ?int $year = null, ?int $regionId = null): array;
}
