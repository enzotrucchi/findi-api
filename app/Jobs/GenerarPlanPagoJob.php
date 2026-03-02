<?php

namespace App\Jobs;

use App\DTOs\PlanPago\PlanPagoDTO;
use App\Services\PlanPagoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerarPlanPagoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<string, mixed>
     */
    public array $dtoData;

    /**
     * Create a new job instance.
     */
    /**
     * @param array<string, mixed> $dtoData
     */
    public function __construct(array $dtoData)
    {
        $this->dtoData = $dtoData;
    }

    /**
     * Execute the job.
     */
    public function handle(PlanPagoService $planPagoService): void
    {
        $dto = \App\DTOs\PlanPago\PlanPagoDTO::desdeArray($this->dtoData);
        $planPagoService->crearPlanPago($dto);
    }
}
