<?php

namespace App\Repositories\Contracts;

interface BookingRepositoryInterface
{
    public function createBooking(array $data);

    public function findTrxIdAndPhoneNumber($bookingTrxId, $phone);

    public function saveToSession(array $data);
    public function updateSessionData(array $data);
    public function getOrderDataFromSession();
    public function clearSession();

}
