<?php

namespace App\Repositories;

use App\Models\Complaint;

class AgencyComplaintRepository
{
    public function getAgencyComplaints($agencyId)
    {
        return Complaint::where('agency_id', $agencyId)
            ->with(['attachments', 'logs'])
            ->get();
    }

    public function findForAgency($id, $agencyId)
    {
        return Complaint::where('id', $id)
            ->where('agency_id', $agencyId)
            ->with(['attachments', 'logs'])
            ->first();
    }

    public function addNote($complaint, $note)
    {
        return $complaint->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'note_added',
            'note'   => $note
        ]);
    }

    public function requestMoreInfo($complaint, $message)
    {
        return $complaint->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'request_additional_info',
            'note'   => $message
        ]);
    }
}
