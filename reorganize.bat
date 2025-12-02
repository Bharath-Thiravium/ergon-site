@echo off
echo ===============================================
echo SAFE PROJECT REORGANIZATION - MOVE SCRIPT
echo ===============================================

REM -----------------------------------------------
REM CREATE ARCHIVE DIRECTORIES
REM -----------------------------------------------
mkdir archive\documentation
mkdir archive\debug_scripts
mkdir archive\test_scripts
mkdir archive\fix_scripts
mkdir archive\sql_dumps
mkdir archive\legacy_unused
mkdir archive\runtime_misc

echo ARCHIVE FOLDERS CREATED.
echo.

REM =====================================================
REM ===============  DOCUMENTATION FILES  ===============
REM =====================================================
for %%F in (
ANALYTICS_ROOT_CAUSE_ANALYSIS.md
ANALYTICS_SPEC.md
BREAK_TIMER_FIX_SUMMARY.md
CALENDAR_DATE_FIX_SUMMARY.md
CHART_CARD_1_IMPLEMENTATION.md
CHART_QUICK_START.md
CHART_REBUILD_SUMMARY.md
CHART_REFACTOR_SUMMARY.md
CHART_RENDERING_FIXES.md
COMPLETE_BACKEND_DELIVERABLES.md
COMPLETE_FINANCE_BACKEND_DELIVERABLES.md
DATA_FLOW_FIX_SUMMARY.md
DELIVERY_CHECKLIST.md
FINAL_5_CHART_FIXES.md
FINANCE_ETL_README.md
FINANCE_SYNC_DELIVERABLES.md
FRONTEND_ONLY_README.md
FRONTEND_PRESERVED_README.md
FUNNEL_CONTAINER_IMPLEMENTATION_COMPLETE.md
HOSTINGER_ALTERNATIVES.md
KPI_CARDS_REFACTORING_SUMMARY.md
MOBILE_DARK_THEME_FIXES_SUMMARY.md
MODULE_SYSTEM_IMPLEMENTATION.md
NEW_FINANCE_MODULE_README.md
NOTIFICATION_SYSTEM_FIX_PLAN.md
NOTIFICATION_SYSTEM_FIXES_SUMMARY.md
NOTIFICATION_UPGRADE_README.md
OPTIMIZATION_SUMMARY.md
SECURITY_ENHANCEMENTS_SUMMARY.md
SLA_TIMER_FIX_COMPLETE.md
STAT_CARD_3_IMPLEMENTATION.md
STAT_CARD_4_IMPLEMENTATION.md
STAT_CARD_5_IMPLEMENTATION.md
TASK_PROGRESS_ENHANCEMENT.md
TIMER_LOGIC_FIXES.md
) do (
  copy "%%F" "archive\documentation\%%F"
  del "%%F"
)

REM =====================================================
REM ===============  DEBUG SCRIPTS  =====================
REM =====================================================
for %%F in (
debug-activities.php
debug_data_fetch.php
debug_data_flow.php
debug_etl_data.php
debug_finance.php
debug_funnel_data.php
debug_funnel_values.php
debug_gst_fields.php
debug_js_api.php
debug_notification_button.php
debug_notifications.php
debug_project_data.php
debug_refresh_error.php
debug_router.php
debug_session.php
) do (
  copy "%%F" "archive\debug_scripts\%%F"
  del "%%F"
)

REM =====================================================
REM ===============  TEST SCRIPTS  ======================
REM =====================================================
for %%F in (
test-analytics-api.html
test-charts-api.php
test-mobile-dark-theme.html
test-prefix.php
test_calendar_fix.php
test_chart_card_1.php
test_company_owner.php
test_complete_data_flow.php
test_complete_gst.php
test_complete_workflow.php
test_etl.php
test_finance_api.php
test_finance_route.php
test_fixes.php
test_fixes_verification.php
test_funnel_implementation.php
test_gst_fix.php
test_kpi_cards.html
test_location_restriction.php
test_mock_api.html
test_modules.php
test_new_finance.php
test_notification_creation.php
test_notification_fix.php
test_notification_fixes.php
test_notification_system.php
test_pause_resume.html
test_planner_direct.php
test_po_debug.php
test_sla_timer_fix.php
test_stat_card_3.php
test_stat_card_4.php
test_stat_card_5.php
) do (
  copy "%%F" "archive\test_scripts\%%F"
  del "%%F"
)

REM =====================================================
REM ===============  FIX / REPAIR SCRIPTS  =============
REM =====================================================
for %%F in (
fix_gst_direct.php
fix_leave_sender_id_final.php
fix_notification_columns.php
fix_notification_reference_ids.php
fix_notification_system.php
fix_notification_system_complete.php
fix_notifications_final.php
fix_notifications_table.php
fix_prefix_filtering.php
fix_sender_id_issue.php
fix_sla_timer_database.php
simple_fix_notifications.php
one_click_fix.php
retrieve_old_css_styles.php
quick_fix_notifications.php
quick_db_fix.php
quick_audit.php
) do (
  copy "%%F" "archive\fix_scripts\%%F"
  del "%%F"
)

REM =====================================================
REM ================== SQL DUMPS  =======================
REM =====================================================
for %%F in (
"ergon_db - Nelson.sql"
"ergon_db(9).sql"
"u494785662_ergon (2).sql"
) do (
  copy %%F archive\sql_dumps\
  del %%F
)

REM =====================================================
REM ================== LEGACY UNUSED  ===================
REM =====================================================
for %%F in (
bulk_move.bat
extracted_old_css_styles.css
root_files.txt
favicon.ico
) do (
  copy "%%F" "archive\legacy_unused\%%F"
  del "%%F"
)

REM =====================================================
REM ================== RUNTIME MISC  ====================
REM =====================================================
for %%F in (
check_system.php
check_table_structure.php
check_tables.php
check_task_status.php
check_tasks.php
check_user_status.php
clean_funnel_api.php
cleanup_duplicate_notifications.php
create_test_notification.php
create_test_notifications.php
data_flow_diagram.php
diagnose_notification_system.php
diagnose_prefix_issue.php
direct_rebuild_notifications.php
final_validation.php
finance.php
finance_debug.php
finance_direct.php
find_users.php
fixed_funnel_api.php
fixed_prefix_api.php
funnel_demo.html
get_csrf_token.php
implement_stat_card_5.php
import_finance_data.php
populate_finance_demo.php
populate_notification_reference_ids.php
rebuild_notifications.php
setup_admin_attendance.php
setup_complete_notifications.php
setup_location_settings.php
setup_modules.php
setup_progress_history.php
simple_test.php
sql_based_filtering.php
validate_environment.php
validate_workflow.php
web_audit_etl.php
web_test_etl.php
worker_start.php
) do (
  copy "%%F" "archive\runtime_misc\%%F"
  del "%%F"
)

echo ===============================================
echo MOVE COMPLETE - ALL FILES SAFELY ARCHIVED
echo ===============================================
pause