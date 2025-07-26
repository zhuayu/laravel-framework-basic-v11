<?php

namespace App\Services\Notification;

class NotificationService
{

    public function getClassify()
    {
        return [
            [
                'name' => 'question',
                'display_name' => '问答广场',
                'children' => [
                    [
                        'name' => 'question_reply',
                        'display_name' => '回复我的',
                        'children' => [
                            [
                                'display_name' => '回答回复',
                                'name' => 'question_answer',
                            ],
                            [
                                'display_name' => '评论回复',
                                'name' => 'question_comment',
                            ],
                            [
                                'display_name' => '回答采纳',
                                'name' => 'question_answer_accepted',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'academy',
                'display_name' => '学社',
                'children' => [
                    [
                        'name' => 'academy_camp',
                        'display_name' => '实战营',
                        'children' => [
                            [
                                'display_name' => '测试',
                                'name' => 'test-notification',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function index($user, $name = '', $names = [], $unread = 0)
    {
        $notifications = $unread ? $user->unreadNotifications() : $user->notifications();
        $dataTypes = $name ? $this->getTypesByName($name) : $names;
        if(!$dataTypes) {
           return $notifications;
        }
        return $notifications->whereIn('data->type', $dataTypes);
    }

    public function readAll($user, $name = '')
    {
        $notifications = $this->index($user, $name, null, 1)->update(['read_at' => now()]);
    }

    public function getChildrenCountByName($user, $name = '')
    {
        $classifys = $this->getClassify();
        $datas = [];
        foreach ($classifys as $data) {
            if ($data['name'] === $name) {
                $datas = $data['children'];
            }
        }
        $datas = count($datas) ? $datas : $classifys;
        return array_map(function ($item) use ($user) {
            $dataTypes = $this->getTypesByName($item['name']);
            return [
                'name' => $item['name'],
                'count' => $user->notifications()
                    ->whereIn('data->type', $dataTypes)
                    ->whereNull('read_at')
                    ->count(),
            ];
        }, $datas);
    }

    public function getCountByRandomNames($user, $names)
    {
        $count = $user->notifications()
            ->whereIn('data->type', $names)
            ->whereNull('read_at')
            ->count();
        return $count;
    }

    public function getTypesByName($name = null)
    {
        $datas = $this->getClassify();
        $locked = $name ? true : false;
        return $this->getTypesByNameDeep($datas, [], $locked, $name);
    }

    private function getTypesByNameDeep($datas, $results = [], $locked = true, $name = '')
    {
        foreach ($datas as $data) {
            $data['locked'] = $locked;
            if ($data['name'] === $name) {
                $data['locked'] = false;
            }
            if (!$data['locked']) {
                array_push($results, $data['name']);
            }
            if (array_key_exists('children', $data)) {
                $results += $this->getTypesByNameDeep($data['children'], $results, $data['locked'], $name);
            }
        }
        return $results;
    }
}
