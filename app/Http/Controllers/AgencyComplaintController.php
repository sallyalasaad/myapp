<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AgencyComplaintRepository;
use App\Repositories\ComplaintRepository;

class AgencyComplaintController extends Controller
{
    protected $agencyRepo;
    protected $commonRepo;

    public function __construct(
        AgencyComplaintRepository $agencyRepo,
        ComplaintRepository $commonRepo
    ) {
        $this->agencyRepo = $agencyRepo;
        $this->commonRepo = $commonRepo;
    }

    // عرض كل الشكاوى التابعة لجهة الموظف
    public function index()
    {
        $agencyId = auth()->user()->agency_id;

        $complaints = $this->agencyRepo->getAgencyComplaints($agencyId);

        return response()->json($complaints, 200);
    }

    // عرض شكوى واحدة
    public function show($id)
    {
        $agencyId = auth()->user()->agency_id;

        $complaint = $this->agencyRepo->findForAgency($id, $agencyId);

        if (!$complaint) {
            return response()->json(['message' => 'الشكوى غير موجودة'], 404);
        }

        return response()->json($complaint, 200);
    }

    // قفل الشكوى
    public function lock($id)
    {
        $agencyId = auth()->user()->agency_id;
        $complaint = $this->agencyRepo->findForAgency($id, $agencyId);

        if (!$complaint) {
            return response()->json(['message' => 'الشكوى غير موجودة'], 404);
        }

        if (!$this->commonRepo->lock($complaint)) {
            return response()->json(['message' => 'الشكوى مقفلة من موظف آخر'], 409);
        }

        return response()->json(['message' => 'تم قفل الشكوى'], 200);
    }

    // فتح الشكوى
    public function unlock($id)
    {
        $agencyId = auth()->user()->agency_id;
        $complaint = $this->agencyRepo->findForAgency($id, $agencyId);

        if (!$complaint) {
            return response()->json(['message' => 'الشكوى غير موجودة'], 404);
        }

        $this->commonRepo->unlock($complaint);

        return response()->json(['message' => 'تم فتح الشكوى'], 200);
    }

    // تغيير حالة الشكوى
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,done,rejected',
            'note' => 'nullable|string'
        ]);

        $agencyId = auth()->user()->agency_id;
        $complaint = $this->agencyRepo->findForAgency($id, $agencyId);

        if (!$complaint) {
            return response()->json(['message' => 'الشكوى غير موجودة'], 404);
        }

        // التأكد من عدم قفل الشكوى من موظف آخر
        if ($complaint->is_locked && $complaint->locked_by != auth()->id()) {
            return response()->json(['message' => 'الشكوى مقفلة من موظف آخر'], 409);
        }

        $this->commonRepo->updateStatus($complaint, $request->status);

        return response()->json(['message' => 'تم تحديث حالة الشكوى'], 200);
    }

    // إضافة ملاحظة
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string'
        ]);

        $agencyId = auth()->user()->agency_id;
        $complaint = $this->agencyRepo->findForAgency($id, $agencyId);

        if (!$complaint) {
            return response()->json(['message' => 'الشكوى غير موجودة'], 404);
        }

        $this->agencyRepo->addNote($complaint, $request->note);

        return response()->json(['message' => 'تم إضافة الملاحظة'], 200);
    }

    // طلب معلومات إضافية من المواطن
    public function requestMoreInfo(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $agencyId = auth()->user()->agency_id;

        $complaint = $this->agencyRepo->findForAgency($id, $agencyId);

        if (!$complaint) {
            return response()->json(['message' => 'الشكوى غير موجودة'], 404);
        }

        $this->agencyRepo->requestMoreInfo($complaint, $request->message);

        return response()->json(['message' => 'تم إرسال طلب معلومات إضافية'], 200);
    }
}

