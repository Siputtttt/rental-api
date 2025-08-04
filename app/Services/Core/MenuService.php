<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;

class MenuService
{
    public function getMenu($groupId = null)
    {
        $menus = DB::table('sx_menus')->orderBy('ordering')->get();

        $filteredMenus = $menus->filter(function ($menu) use ($groupId) {
            $accessData = json_decode($menu->access_data, true);
            return isset($accessData[$groupId]) && $accessData[$groupId] == 1;
        });

        if (!$groupId) {
            $filteredMenus = $menus = DB::table('sx_menus')->where('active', '1')->orderBy('ordering')->get();
            $menu = self::getMenuTree($filteredMenus);
        } else {
            $menu = self::getMenuTreeUser($filteredMenus);
        }
        return $menu;
    }

    public function getMenuTreeUser($menus)
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == 0) {
                $children = self::getMenuChildrenUser($menus, $menu->menu_id);
                $node = [
                    'id' => $menu->menu_id,
                    'name_title' => $menu->menu_name,
                    'type' => $menu->menu_type,
                    'module' => '/' . $menu->module,
                    'menu_icons' => $menu->menu_icons,
                    'position' => $menu->position,
                    'children' => $children,
                ];

                if (empty($children)) {
                    $node['children'] = [];
                }

                $tree[] = $node;
            }
        }

        return $tree;
    }

    public function getMenuChildrenUser($menus, $parentId)
    {
        $children = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $childNodes = self::getMenuChildrenUser($menus, $menu->menu_id);
                $node = [
                    'id' => $menu->menu_id,
                    'name_title' => $menu->menu_name,
                    'type' => $menu->menu_type,
                    'module' => '/' . $menu->module,
                    'menu_icons' => $menu->menu_icons,
                    'position' => $menu->position,
                    'children' => $childNodes,
                ];

                if (empty($childNodes)) {
                    $node['children'] = [];
                }

                $children[] = $node;
            }
        }
        return $children;
    }

    public function getMenuTree($menus)
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == 0) {
                $children = self::getMenuChildren($menus, $menu->menu_id);
                $node = [
                    'id' => $menu->menu_id,
                    'name' => $menu->menu_name,
                    'type' => $menu->menu_type,
                    'module' => $menu->module,
                    'children' => $children,
                    'expanded' => true
                ];

                if (empty($children)) {
                    $node['children'] = [];
                    $node['expanded'] = false;
                }

                $tree[] = $node;
            }
        }

        return $tree;
    }

    public function getMenuChildren($menus, $parentId)
    {
        $children = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $childNodes = self::getMenuChildren($menus, $menu->menu_id);
                $node = [
                    'id' => $menu->menu_id,
                    'name' => $menu->menu_name,
                    'type' => $menu->menu_type,
                    'module' => $menu->module,
                    'children' => $childNodes,
                    'expanded' => true
                ];

                if (empty($childNodes)) {
                    $node['children'] = [];
                    $node['expanded'] = false;
                }

                $children[] = $node;
            }
        }
        return $children;
    }

    public function saveNestedMenus($menu, &$index = 0, $parentId = 0)
    {
        $result = [];

        foreach ($menu as $item) {
            $index++;

            $result[] = [
                'menu_id'   => $item['id'],
                'parent_id' => $parentId,
                'ordering'  => $index,
            ];

            if (!empty($item['children']) && is_array($item['children'])) {
                $result = array_merge($result, $this->saveNestedMenus($item['children'], $index, $item['id']));
            }
        }

        foreach ($result as $item) {
            DB::table('sx_menus')->where('menu_id', $item['menu_id'])->update([
                'parent_id' => $item['parent_id'],
                'ordering'  => $item['ordering'],
            ]);
        }

        return $result;
    }
}
