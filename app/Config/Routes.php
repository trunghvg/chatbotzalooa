<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================================
// WEBHOOK ZALO OA (public - khong can dang nhap)
// =====================================================================
$routes->get('webhook', 'Webhook::verify');
$routes->post('webhook', 'Webhook::receive');

// =====================================================================
// ADMIN PANEL
// =====================================================================

// Trang chu (chuyen huong den admin)
$routes->get('/', 'Admin::index');

// Dang nhap / Dang xuat
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/login', 'Auth::doLogin');
$routes->get('auth/logout', 'Auth::logout');

// Dashboard
$routes->get('admin', 'Admin::index');
$routes->get('admin/dashboard', 'Admin::index');

// Quan ly cuoc hoi thoai
$routes->get('admin/conversations', 'Admin::conversations');
$routes->get('admin/conversations/(:num)', 'Admin::conversationDetail/$1');
$routes->delete('admin/conversations/(:num)', 'Admin::deleteConversation/$1');

// Quan ly tin nhan
$routes->get('admin/messages', 'Admin::messages');

// Cai dat he thong
$routes->get('admin/settings', 'Admin::settings');
$routes->post('admin/settings', 'Admin::saveSettings');

// Cai dat system prompt cho Claude
$routes->get('admin/prompt', 'Admin::prompt');
$routes->post('admin/prompt', 'Admin::savePrompt');

// Knowledge Base - co so kien thuc cho chatbot
$routes->get('admin/knowledge', 'Admin::knowledge');
$routes->get('admin/knowledge/create', 'Admin::knowledgeCreate');
$routes->post('admin/knowledge/store', 'Admin::knowledgeStore');
$routes->get('admin/knowledge/edit/(:num)', 'Admin::knowledgeEdit/$1');
$routes->post('admin/knowledge/update/(:num)', 'Admin::knowledgeUpdate/$1');
$routes->delete('admin/knowledge/(:num)', 'Admin::knowledgeDelete/$1');
$routes->post('admin/knowledge/toggle/(:num)', 'Admin::knowledgeToggle/$1');
$routes->post('admin/knowledge/import', 'Admin::knowledgeImport');

// API cho admin (AJAX)
$routes->get('admin/api/stats', 'Admin::apiStats');
$routes->post('admin/api/test-claude', 'Admin::testClaude');
$routes->post('admin/api/test-zalo', 'Admin::testZalo');
$routes->post('admin/api/refresh-token', 'Admin::refreshZaloToken');
$routes->get('admin/api/conversations/(:num)/messages', 'Admin::apiConversationMessages/$1');
$routes->get('admin/api/conversations/list', 'Admin::apiConversationsList');
$routes->post('admin/api/refresh-user-names', 'Admin::refreshUserNames');

// =====================================================================
// ZALO OAUTH CALLBACK
// =====================================================================
$routes->get('zalo/callback', 'ZaloAuth::callback');
$routes->get('zalo/authorize', 'ZaloAuth::authorize');
