<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Notification\NotificationService;
use App\Http\Requests\Api\Web\Notification\NotificationIndexRequest;
use App\Http\Requests\Api\Web\Notification\NotificationByNameRequest;
use App\Http\Requests\Api\Web\Notification\NotificationByNamesRequest;
use App\Http\Resources\Api\Web\Notification\NotificationIndexResource;
use App\Http\Resources\PaginationCollection;
use Auth;

class NotificationController extends Controller
{
    public function index(NotificationIndexRequest $request) {
        $data = $request->validated();
        $user = Auth::user();
        $api = new NotificationService();
        $notifications = $api->index($user, $request->name, $request->names, $request->unread);
        $notifications = $notifications->paginate($request->input('page_size', 10));
        return new PaginationCollection(NotificationIndexResource::collection($notifications));
    }

    public function count(NotificationByNameRequest $request) {
        $data = $request->validated();
        $user = Auth::user();
        $api = new NotificationService();
        $datas = $api->getChildrenCountByName($user, $request->name);
        return $this->success($datas);
    }

    public function countByNames(NotificationByNamesRequest $request) {
        $user = Auth::user();
        $api = new NotificationService();
        $datas = $api->getCountByRandomNames($user, $request->names);
        return $this->success($datas);
    }

    public function show($id) {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)->first();
        return $this->success($notification);
    }

    public function update(Request $request, $id) {
        $user = Auth::user();
        $notification = $user
            ->notifications()
            ->where('id', $id)->first();

        if(!$notification->read_at) {
            $notification->markAsRead();
        }

        return $this->success([
            'status' => $status,
            'read_at' => $notification->read_at
        ]);
    }

    public function readAll(Request $request) {
        $user = Auth::user();
        $api = (new NotificationService())->readAll($user, $request->name);
        return $this->success(null, '全部已读');
    }
}
