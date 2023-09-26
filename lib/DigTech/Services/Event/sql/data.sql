USE eventmgr;

INSERT INTO event_class (class_code, class_description) VALUES
( 'order', 'Sales Order Events' ),
( 'shipment', 'Shipment Events' );

INSERT INTO event_performer (performer_code, performer_description) VALUES
( 'Import', 'Import processing' ),
( 'Normalize', 'Normalization process' ),
( 'Receiving', 'Receiving process' );
