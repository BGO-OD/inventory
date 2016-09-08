--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: fuzzystrmatch; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS fuzzystrmatch WITH SCHEMA public;


--
-- Name: EXTENSION fuzzystrmatch; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION fuzzystrmatch IS 'determine similarities and distance between strings';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: files; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE files (
    file_name text,
    mimetype text,
    file oid,
    size integer,
    inserted timestamp with time zone DEFAULT now()
);


ALTER TABLE files OWNER TO inventory;

--
-- Name: maintenance; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE maintenance (
    maintenance_id integer NOT NULL,
    id integer,
    date timestamp without time zone,
    status text,
    comment text,
    responsible integer
);


ALTER TABLE maintenance OWNER TO inventory;

--
-- Name: maintenance_maintenance_id_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE maintenance_maintenance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE maintenance_maintenance_id_seq OWNER TO inventory;

--
-- Name: maintenance_maintenance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE maintenance_maintenance_id_seq OWNED BY maintenance.maintenance_id;


--
-- Name: model_weblinks; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE model_weblinks (
    model integer,
    link text,
    comment text,
    linkid integer NOT NULL
);


ALTER TABLE model_weblinks OWNER TO inventory;

--
-- Name: model_weblinks_linkid_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE model_weblinks_linkid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE model_weblinks_linkid_seq OWNER TO inventory;

--
-- Name: model_weblinks_linkid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE model_weblinks_linkid_seq OWNED BY model_weblinks.linkid;


--
-- Name: models; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE models (
    model integer NOT NULL,
    type text,
    manufacturer text,
    name text,
    maintenance_interval interval,
    maintenance_instructions text,
    comment text,
    sublocations text,
    description text
);


ALTER TABLE models OWNER TO inventory;

--
-- Name: models_model_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE models_model_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE models_model_seq OWNER TO inventory;

--
-- Name: models_model_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE models_model_seq OWNED BY models.model;


--
-- Name: object_files; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE object_files (
    object integer,
    link text,
    comment text,
    linkid integer NOT NULL
);


ALTER TABLE object_files OWNER TO inventory;

--
-- Name: object_files_linkid_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE object_files_linkid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE object_files_linkid_seq OWNER TO inventory;

--
-- Name: object_files_linkid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE object_files_linkid_seq OWNED BY object_files.linkid;


--
-- Name: objects; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE objects (
    id integer NOT NULL,
    added timestamp without time zone,
    model integer,
    serial text,
    location integer,
    comment text,
    institute_inventory_number text,
    object_name text,
    order_number text,
    ownerid integer,
    next_maintenance timestamp without time zone,
    location_description text,
    echeck_inventory_number text
);


ALTER TABLE objects OWNER TO inventory;

--
-- Name: objects_id_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE objects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE objects_id_seq OWNER TO inventory;

--
-- Name: objects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE objects_id_seq OWNED BY objects.id;


--
-- Name: order_weblinks; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE order_weblinks (
    orderlinkid integer NOT NULL,
    number integer,
    comment text,
    link oid
);


ALTER TABLE order_weblinks OWNER TO inventory;

--
-- Name: order_weblinks_orderlinkid_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE order_weblinks_orderlinkid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE order_weblinks_orderlinkid_seq OWNER TO inventory;

--
-- Name: order_weblinks_orderlinkid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE order_weblinks_orderlinkid_seq OWNED BY order_weblinks.orderlinkid;


--
-- Name: ordercomments; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE ordercomments (
    number integer,
    date timestamp without time zone,
    comment text,
    commentid integer NOT NULL
);


ALTER TABLE ordercomments OWNER TO inventory;

--
-- Name: ordercomments_commentid_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE ordercomments_commentid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ordercomments_commentid_seq OWNER TO inventory;

--
-- Name: ordercomments_commentid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE ordercomments_commentid_seq OWNED BY ordercomments.commentid;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE orders (
    number integer NOT NULL,
    name text,
    orderdate timestamp without time zone,
    invoicedate timestamp without time zone,
    state text,
    account text,
    netto double precision,
    brutto double precision,
    currency text,
    amount double precision,
    besteller integer,
    signed_by integer,
    company text,
    ordernumber text
);


ALTER TABLE orders OWNER TO inventory;

--
-- Name: orders_number_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE orders_number_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE orders_number_seq OWNER TO inventory;

--
-- Name: orders_number_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE orders_number_seq OWNED BY orders.number;


--
-- Name: owners; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE owners (
    ownerid integer NOT NULL,
    owner_name text
);


ALTER TABLE owners OWNER TO inventory;

--
-- Name: owners_ownerid_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE owners_ownerid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE owners_ownerid_seq OWNER TO inventory;

--
-- Name: owners_ownerid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE owners_ownerid_seq OWNED BY owners.ownerid;


--
-- Name: usage; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE usage (
    id integer,
    userid integer,
    validfrom timestamp without time zone DEFAULT now(),
    validto timestamp without time zone DEFAULT 'infinity'::timestamp without time zone,
    comment text
);


ALTER TABLE usage OWNER TO inventory;

--
-- Name: users; Type: TABLE; Schema: public; Owner: inventory; Tablespace: 
--

CREATE TABLE users (
    userid integer NOT NULL,
    name text
);


ALTER TABLE users OWNER TO inventory;

--
-- Name: users_userid_seq; Type: SEQUENCE; Schema: public; Owner: inventory
--

CREATE SEQUENCE users_userid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE users_userid_seq OWNER TO inventory;

--
-- Name: users_userid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: inventory
--

ALTER SEQUENCE users_userid_seq OWNED BY users.userid;


--
-- Name: maintenance_id; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY maintenance ALTER COLUMN maintenance_id SET DEFAULT nextval('maintenance_maintenance_id_seq'::regclass);


--
-- Name: linkid; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY model_weblinks ALTER COLUMN linkid SET DEFAULT nextval('model_weblinks_linkid_seq'::regclass);


--
-- Name: model; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY models ALTER COLUMN model SET DEFAULT nextval('models_model_seq'::regclass);


--
-- Name: linkid; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY object_files ALTER COLUMN linkid SET DEFAULT nextval('object_files_linkid_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY objects ALTER COLUMN id SET DEFAULT nextval('objects_id_seq'::regclass);


--
-- Name: orderlinkid; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY order_weblinks ALTER COLUMN orderlinkid SET DEFAULT nextval('order_weblinks_orderlinkid_seq'::regclass);


--
-- Name: commentid; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY ordercomments ALTER COLUMN commentid SET DEFAULT nextval('ordercomments_commentid_seq'::regclass);


--
-- Name: number; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY orders ALTER COLUMN number SET DEFAULT nextval('orders_number_seq'::regclass);


--
-- Name: ownerid; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY owners ALTER COLUMN ownerid SET DEFAULT nextval('owners_ownerid_seq'::regclass);


--
-- Name: userid; Type: DEFAULT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY users ALTER COLUMN userid SET DEFAULT nextval('users_userid_seq'::regclass);


--
-- Name: order_weblinks_pkey; Type: CONSTRAINT; Schema: public; Owner: inventory; Tablespace: 
--

ALTER TABLE ONLY order_weblinks
    ADD CONSTRAINT order_weblinks_pkey PRIMARY KEY (orderlinkid);


--
-- Name: orders_pkey; Type: CONSTRAINT; Schema: public; Owner: inventory; Tablespace: 
--

ALTER TABLE ONLY orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (number);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: inventory; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (userid);


--
-- Name: ordercomments_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY ordercomments
    ADD CONSTRAINT ordercomments_number_fkey FOREIGN KEY (number) REFERENCES orders(number);


--
-- Name: orders_order_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY orders
    ADD CONSTRAINT orders_order_by_fkey FOREIGN KEY (besteller) REFERENCES users(userid);


--
-- Name: orders_signed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: inventory
--

ALTER TABLE ONLY orders
    ADD CONSTRAINT orders_signed_by_fkey FOREIGN KEY (signed_by) REFERENCES users(userid);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: ordercomments; Type: ACL; Schema: public; Owner: inventory
--

REVOKE ALL ON TABLE ordercomments FROM PUBLIC;
REVOKE ALL ON TABLE ordercomments FROM inventory;
GRANT ALL ON TABLE ordercomments TO inventory;


--
-- Name: orders; Type: ACL; Schema: public; Owner: inventory
--

REVOKE ALL ON TABLE orders FROM PUBLIC;
REVOKE ALL ON TABLE orders FROM inventory;
GRANT ALL ON TABLE orders TO inventory;


--
-- PostgreSQL database dump complete
--

