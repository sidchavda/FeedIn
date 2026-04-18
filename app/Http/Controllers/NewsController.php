<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Category;
use App\Models\Language;
use App\Models\Country;
use App\Models\State;
use App\Models\DeviceToken;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request)
    {
        $query = News::with(['country', 'state', 'language', 'category'])->latest();
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('lcategory', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhereHas('country', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('state', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('language', function($lq) use ($search) {
                      $lq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $news = $query->paginate(10);
        
        if ($request->has('search')) {
            $news->appends(['search' => $request->search]);
        }
        
        return view('pages.news.index', compact('news'));
    }

    public function add()
    {
        $categories = Category::all();
        $languages = Language::where('is_active', 1)->get();
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $states = collect();
        
        // If editing, load states for the selected country
        if (request()->has('id')) {
            $news = News::find(request('id'));
            if ($news && $news->country_id) {
                $states = State::where('country_id', $news->country_id)->where('is_active', 1)->orderBy('name')->get();
            }
        }
        
        return view('pages.news.add', compact('categories', 'languages', 'countries', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:categories,id',
            'link' => 'nullable|url',
        ]);

        $data = $request->except('_token');
        
        // Handle status field (checkbox returns nothing when unchecked)
        $data['status'] = $request->has('status') ? 1 : 0;
        
        // Handle push_notification field (checkbox returns nothing when unchecked)
        $data['push_notification'] = $request->has('push_notification') ? 1 : 0;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/news'), $imageName);
            $data['image'] = 'images/news/' . $imageName;
        }

        $news = News::create($data);

        // Send push notification if enabled
        if ($news->push_notification && $news->status) {
            $this->sendPushNotification($news);
        }

        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);
        $categories = Category::all();
        $languages = Language::where('is_active', 1)->get();
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $states = collect();
        
        if ($news->country_id) {
            $states = State::where('country_id', $news->country_id)->where('is_active', 1)->orderBy('name')->get();
        }
        
        return view('pages.news.add', compact('news', 'categories', 'languages', 'countries', 'states'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:categories,id',
            'link' => 'nullable|url',
        ]);

        $news = News::findOrFail($id);
        $data = $request->except(['_token', '_method']);
        
        // Handle status field (checkbox returns nothing when unchecked)
        $data['status'] = $request->has('status') ? 1 : 0;
        
        // Handle push_notification field (checkbox returns nothing when unchecked)
        $data['push_notification'] = $request->has('push_notification') ? 1 : 0;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/news'), $imageName);
            $data['image'] = 'images/news/' . $imageName;
        }

        $news->update($data);

        // Send push notification if enabled and status is active
        if ($news->push_notification && $news->status) {
            $this->sendPushNotification($news);
        }

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    /**
     * Send push notification to all device tokens
     */
    private function sendPushNotification($news)
    {
        try {
            // Get all device tokens (including guest tokens where user_id is null)
            $deviceTokens = DeviceToken::pluck('device_token')->toArray();

            if (empty($deviceTokens)) {
                Log::info('No device tokens found for push notification');
                return;
            }

            // Prepare notification data (all values must be strings for Firebase)
            $title = 'New News: ' . $news->title;
            $body = substr(strip_tags($news->description), 0, 100) . '...';
            $data = [
                'news_id' => (string) $news->id,
                'slug' => (string) $news->slug,
                'type' => 'news',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            // Send to all devices
            $result = $this->fcmService->sendToMultipleDevices($deviceTokens, $title, $body, $data);

            if ($result) {
                // Log::info('Push notification sent successfully for news ID: ' . $news->id);
            } else {
                Log::error('Failed to send push notification for news ID: ' . $news->id);
            }
        } catch (\Exception $e) {
            Log::error('Error sending push notification: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $news = News::findOrFail($id);
        $news->delete();
        return redirect()->back()->with('success', 'News deleted successfully.');
    }
}
