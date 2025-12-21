<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ShoppingListController extends Controller
{
    protected function ensureList(): ShoppingList
    {
        $user = Auth::user();
        return ShoppingList::firstOrCreate(['user_id' => $user->id], ['name' => 'My Shopping List']);
    }

    public function index()
    {
        $list = $this->ensureList();
        $items = $list->items()->get();
        return view('shopping_list.index', compact('list', 'items'));
    }

    public function storeItem(Request $request)
    {
        $list = $this->ensureList();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:255',
        ]);
        $maxOrder = $list->items()->max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;
        $list->items()->create($data);
        return Redirect::back();
    }

    public function toggleItem(ShoppingListItem $item)
    {
        $list = $this->ensureList();
        if ($item->shopping_list_id !== $list->id) abort(403);
        $item->is_checked = !$item->is_checked;
        $item->save();
        return Redirect::back();
    }

    public function updateItem(Request $request, ShoppingListItem $item)
    {
        $list = $this->ensureList();
        if ($item->shopping_list_id !== $list->id) abort(403);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:255',
        ]);
        $item->update($data);
        return Redirect::back();
    }

    public function deleteItem(ShoppingListItem $item)
    {
        $list = $this->ensureList();
        if ($item->shopping_list_id !== $list->id) abort(403);
        $item->delete();
        return Redirect::back();
    }

    public function clearChecked()
    {
        $list = $this->ensureList();
        ShoppingListItem::where('shopping_list_id', $list->id)->where('is_checked', true)->delete();
        return Redirect::back();
    }

    public function markAllChecked()
    {
        $list = $this->ensureList();
        ShoppingListItem::where('shopping_list_id', $list->id)->where('is_checked', false)->update(['is_checked' => true]);
        return Redirect::back();
    }

    public function addFromRecipe(Recipe $recipe)
    {
        $list = $this->ensureList();
        $maxOrder = $list->items()->max('sort_order') ?? 0;

        if ($recipe->ingredients) {
            foreach ($recipe->ingredients as $ingredient) {
                $titleParts = [];
                if (!empty($ingredient->amount)) { $titleParts[] = trim($ingredient->amount); }
                if (!empty($ingredient->name)) { $titleParts[] = trim($ingredient->name); }
                $title = trim(implode(' ', $titleParts));
                if ($title === '') continue;
                $list->items()->create([
                    'title' => $title,
                    'quantity' => null,
                    'is_checked' => false,
                    'sort_order' => ++$maxOrder,
                ]);
            }
        }

        return Redirect::route('shopping-list.index')->with('status', 'Ingredients added to your shopping list');
    }
}
