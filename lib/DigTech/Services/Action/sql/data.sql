USE actionmgr;

INSERT INTO action_class (class_code, class_description) VALUES
( 'order', 'Sales Order Actions' ),
( 'shipment', 'Shipment Actions' );

INSERT INTO action_performer (performer_code, performer_description) VALUES
( 'Import', 'Import processing' ),
( 'Normalize', 'Normalization process' ),
( 'Receiving', 'Receiving process' );
