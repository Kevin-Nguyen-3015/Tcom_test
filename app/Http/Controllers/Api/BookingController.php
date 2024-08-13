<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {

        // Lấy dữ liệu di validate
        $rqData = $request->validate([
            'name' => 'required|string|max:256',
            'space_id' => 'required|integer|exists:spaces,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
        ]);

        //Không phải là quá khứ
        if (Carbon::parse($rqData['end_time'])->isPast() || Carbon::parse($rqData['start_time'])->isPast()) {
            return response()->json(['error' => 'Cannot book a space in the past.'], 422);
        }

        //Tách start_time và end_time
        $startDateTime = Carbon::parse($rqData['start_time']);
        $endDateTime = Carbon::parse($rqData['end_time']);

        if ($startDateTime->diffInHours($endDateTime) < 1) {
            return response()->json([
                'error' => 'Rent time should be at least one hour',
            ], 422);
        }

        $currentDate = $startDateTime->copy();
        $endDatePlusOneDay = $endDateTime->copy()->addDay();

        DB::beginTransaction();

        try {
            $bookings = []; // Khởi tạo mảng để lưu các booking đã tạo

            while ($currentDate->lt($endDatePlusOneDay)) {
                if ($currentDate->isSameDay($startDateTime)) {
                    $startTime = $startDateTime->format('H:i');
                } else {
                    $startTime = '00:00';
                }
            
                if ($currentDate->isSameDay($endDateTime)) {
                    $endTime = $endDateTime->format('H:i');
                } else {
                    $endTime = '24:00';
                }

                // Kiểm tra trùng lặp với booking trước đó
                $overlappingBookings = Booking::where('space_id', $rqData['space_id'])
                    ->where('date_booking', $currentDate->format('Ymd'))
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function($query) use ($startTime, $endTime) {
                                  $query->where('start_time', '<=', $startTime)
                                        ->where('end_time', '>=', $endTime);
                              });
                    })
                    ->exists();
        
                if ($overlappingBookings) {
                    // Rollback nếu gãy
                    DB::rollBack();
                    return response()->json(['error' => 'Time slot is already booked'], 422);
                }
        
                // Lưu booking cho ngày đó
                $booking = Booking::create([
                    'name' => $rqData['name'],
                    'space_id' => $rqData['space_id'],
                    'date_booking' => $currentDate->format('Ymd'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

                // Thêm booking vào mảng kết quả
                $bookings[] = [
                    'id' => $booking->id,
                    'space_id' => $booking->space_id,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'date_booking' => $booking->date_booking,
                ];

                // Mỗi ngày lưu một dòng
                $currentDate->addDay();
            }

            // Commit transaction nếu tất cả đều thành công
            DB::commit();

            return response()->json([
                'success' => 'Booking created successfully.',
                'data' => $bookings,
            ], 201);

        } catch (\Exception $e) {
            // Rollback trong trường hợp có lỗi
            DB::rollBack();
            return response()->json(['error' => 'Booking failed: ' . $e->getMessage()], 500);
        }
    }
}