<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    /**
     * Create a new announcement.
     */
    public function create(array $data)
    {
        if ($data['type'] === 'image' && request()->hasFile('content')) {
            // Handle file upload for image type announcements.
            $file = request()->file('content');
            $filePath = $file->store('announcements', 'public');
            $data['content'] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => Storage::url($filePath),
                'mime_type' => $file->getClientMimeType(),
                'size'      => $file->getSize(),
            ];
        } else {
            // For text announcements, expect JSON data with title and body.
            $content = json_decode($data['content'], true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                throw new \Exception('Invalid content format.');
            }
            $data['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
        }

        Announcement::create($data);
    }

    /**
     * Update an existing announcement.
     */
    public function update(Announcement $announcement, array $data)
    {
        if ($data['type'] === 'image') {
            if (request()->hasFile('content')) {
                // Delete previous image if it exists.
                $currentContent = $announcement->content;
                if (is_array($currentContent) && isset($currentContent['file_path'])) {
                    $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                // Upload new file.
                $file = request()->file('content');
                $filePath = $file->store('announcements', 'public');
                $data['content'] = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => Storage::url($filePath),
                    'mime_type' => $file->getClientMimeType(),
                    'size'      => $file->getSize(),
                ];
            } else {
                // If no new file is provided, do not update the content.
                unset($data['content']);
            }
        } else {
            // For text type announcements: if the previous type was image, delete the stored image.
            $currentContent = $announcement->content;
            if (is_array($currentContent) && isset($currentContent['file_path'])) {
                $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
            // Decode the JSON content.
            $content = json_decode($data['content'], true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                throw new \Exception('Invalid content format.');
            }
            $data['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
        }

        $announcement->update($data);
    }

    /**
     * Delete an announcement and remove associated files if necessary.
     */
    public function delete(Announcement $announcement)
    {
        $currentContent = $announcement->content;
        if (is_array($currentContent) && isset($currentContent['file_path'])) {
            $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $announcement->delete();
    }
}
