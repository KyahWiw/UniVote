<?php
function getEventBanner($db, $event_id) {
    $query = $db->prepare("SELECT event_banner FROM election_events WHERE id = ?");
    $query->execute([$event_id]);
    $event = $query->fetch(PDO::FETCH_ASSOC);

    if ($event && !empty($event['event_banner']) && file_exists(__DIR__ . '/../../Manage Election/uploads/' . $event['event_banner'])) {
        return '../../Manage Election/uploads/' . $event['event_banner'];
    }

    return '../../uploads/default-banner.jpg';
}
?>