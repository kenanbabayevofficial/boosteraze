.class Lcom/mikasa/codm/l;
.super Landroid/os/Handler;


# static fields
.field private static final short:[S


# instance fields
.field private final a:Z

.field private final b:Z

.field private final c:Ljava/lang/String;

.field private final d:Z

.field private final e:Landroid/content/Context;

.field private final f:Landroid/app/ProgressDialog;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0x18

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/l;->short:[S

    return-void

    :array_0
    .array-data 2
        0x13ds
        0x136s
        0x133s
        0x131s
        0x13as
        0x17es
        0x169s
        0x16bs
        0x16bs
        0x17es
        0x141s
        0x7dbs
        0x7e7s
        0x7ebs
        0x7e3s
        0x7eds
        0x7fcs
        0x2221s
        0x533s
        0x504s
        0x504s
        0x519s
        0x504s
        0x2225s
    .end array-data
.end method

.method native constructor <init>(ZZLjava/lang/String;ZLandroid/content/Context;Landroid/app/ProgressDialog;)V
.end method

.method public static native ۣ۟۠ۡۢ(Ljava/lang/Object;)Landroid/content/Context;
.end method

.method public static native ۟ۥ۟ۡۨ(Ljava/lang/Object;)Ljava/lang/String;
.end method

.method public static native ۟ۥ۠۠ۧ(Ljava/lang/Object;)Z
.end method

.method public static native ۟ۥۢۧۦ(Ljava/lang/Object;)Z
.end method

.method public static native ۟ۦۧۦۣ(Ljava/lang/Object;)Landroid/app/ProgressDialog;
.end method

.method public static native ۡۧ۠ۤ()[S
.end method

.method public static native ۣۤۡۢ(Ljava/lang/Object;)V
.end method

.method public static native ۣۧ۠ۧ(Ljava/lang/Object;)Z
.end method


# virtual methods
.method public native handleMessage(Landroid/os/Message;)V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
