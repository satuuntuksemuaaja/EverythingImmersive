<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\Category;
use App\Organizer;
use App\Http\Requests\EventRequest;
use Illuminate\Support\Facades\Storage;

class EventsController extends Controller
{
    public function __construct(Event $event)
    {
        $this->middleware('auth')->except(['index','show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::whereHas('events')->oldest()->get();
        //This works, but not with infinite loading
        $events = Event::latest()->where('approved', true)->get();
        //I cant figure out how to get this to work with infinite loading. 
        //$events = Event::latest()->where('approved', true)->paginate(4);
        return view('events.index',compact('events','categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $event = Event::create([
            'user_id' => auth()->id(),
            'slug' => rand()
        ]);

        return redirect('/create-your-event/'. $event->slug . '/location');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {  
        $this->authorize('showApproved', $event);
        $category = Category::find($event->category_id);

        return view('events.show', [
            'event' => $event,
            'category' => $category,
            'organizer' => Organizer::find($event->organizer_id),
            'similarEvents' => Event::SimilarCity($event)->UniqueCity($event)->inRandomOrder()->limit(3)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $this->authorize('update', $event);
        Storage::delete('public/' . $event->eventImagePath);
        Storage::delete('public/' . $event->thumbImagePath);
        $event->delete();
        return redirect()->back();
    }
    public function cities(Request $request)
    {
        return $request;
    }

}
