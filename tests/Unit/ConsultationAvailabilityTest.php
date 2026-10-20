<?php

namespace Tests\Unit;

use App\Models\Consultant;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Tests\TestCase;

class ConsultationAvailabilityTest extends TestCase
{
    public function test_a_slot_is_a_sixty_minute_range(): void
    {
        $this->assertSame('11:00–12:00', ConsultationAvailability::slotRange('11:00'));
        $this->assertSame('21:00–22:00', ConsultationAvailability::slotRange('21:00'));
    }

    public function test_a_party_must_fit_the_area_capacity(): void
    {
        $area = new Consultant(['min_participants' => 1, 'max_participants' => 6]);
        $availability = new ConsultationAvailability;

        $this->assertFalse($availability->fitsParty($area, 0));
        $this->assertTrue($availability->fitsParty($area, 1));
        $this->assertTrue($availability->fitsParty($area, 6));
        $this->assertFalse($availability->fitsParty($area, 7));
    }
}
